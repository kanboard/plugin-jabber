<?php

namespace Kanboard\Plugin\Jabber\Notification;

use Exception;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Protocol\Message;
use Fabiang\Xmpp\Protocol\Presence;
use Kanboard\Core\Base;
use Kanboard\Core\Notification\NotificationInterface;
use Kanboard\Model\TaskModel;
use Kanboard\Model\CommentModel;
use Kanboard\Model\SubtaskModel;

/**
 * Jabber Notification
 *
 * @package  notification
 * @author   Frederic Guillot
 */
class Jabber extends Base implements NotificationInterface
{
    /**
     * Send notification to a user
     *
     * @access public
     * @param  array     $user
     * @param  string    $event_name
     * @param  array     $event_data
     */
    public function notifyUser(array $user, $event_name, array $event_data)
    {
        try {
            $jid = $this->userMetadataModel->get($user['id'], 'jabber_jid');

            if (! empty($jid)) {
                $project = ($event_name === TaskModel::EVENT_OVERDUE) ? $this->projectModel->getByName($event_data['project_name']) : $project = $this->projectModel->getById($event_data['task']['project_id']);

                $client = $this->getClient();

                $message = new Message;
                $message->setMessage($this->getMessage($project, $event_name, $event_data))
                        ->setTo($jid);

                $client->send($message);
                $client->disconnect();

            }

        } catch (Exception $e) {
            $this->logger->error('Jabber error: '.$e->getMessage());
        }
    }

    /**
     * Send notification to a project
     *
     * @access public
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     */
    public function notifyProject(array $project, $event_name, array $event_data)
    {
        try {
            $room = $this->projectMetadataModel->get($project['id'], 'jabber_room');

            if (! empty($room)) {
                $client = $this->getClient();

                $channel = new Presence;
                $channel->setTo($room)->setNickname($this->configModel->get('jabber_nickname'));
                $client->send($channel);

                $message = new Message;
                $message->setMessage($this->getMessage($project, $event_name, $event_data))
                        ->setTo($room)
                        ->setType(Message::TYPE_GROUPCHAT);

                $client->send($message);
                $client->disconnect();
            }

        } catch (Exception $e) {
            $this->logger->error('Jabber error: '.$e->getMessage());
        }
    }

    /**
     * Get Jabber client
     *
     * @access public
     * @return \Fabiang\Xmpp\Client
     */
    public function getClient()
    {
        $options = new Options($this->configModel->get('jabber_server'));
        $options->setUsername($this->configModel->get('jabber_username'));
        $options->setPassword($this->configModel->get('jabber_password'));
        $options->setTo($this->configModel->get('jabber_domain'));
        $options->setLogger($this->logger);

        return new Client($options);
    }

    /**
     * Get message to send
     *
     * @access public
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     * @return string
     */
    public function getMessage(array $project, $event_name, array $event_data)
    {
        if ($this->userSession->isLogged()) {
            $author = $this->helper->user->getFullname();
            $title = $this->notificationModel->getTitleWithAuthor($author, $event_name, $event_data);
        } else {
            $title = $this->notificationModel->getTitleWithoutAuthor($event_name, $event_data);
        }

        $payload = '['.$project['name'].'] ';
        $payload .= $title.":\n";

        if ($event_name === TaskModel::EVENT_OVERDUE) {
            // Its an overdue event, there could be more tasks inside $event_data
            foreach($event_data['tasks'] as $task) {
                $payload .= $task['title'].' (#'.$task['id'].') - '.date('D, d M Y', $task['date_due'])."\n";
                if ($this->configModel->get('application_url') !== '') {
                    $payload .= $this->helper->url->to('TaskViewController', 'show', array('task_id' => $task['id'], 'project_id' => $project['id']), '', true)."\n\n";
                }
            }
        } else {
            // Event with only one task
            $payload .= $event_data['task']['title'].' (#'.$event_data['task']['id'].")\n";
            //if (preg_match('/^comment/', $event_name)) {
            if (in_array($event_name, array(CommentModel::EVENT_UPDATE, CommentModel::EVENT_CREATE, CommentModel::EVENT_DELETE))) {
                // Add the actual comment to the message
                $payload .= $event_data['comment']['comment']."\n";
            } else if ($event_name === TaskModel::EVENT_UPDATE) {
                // Add the updated fields to the message
                $payload .= isset($event_data['changes']['column_title']) ? t('Column:').' '.$event_data['changes']['column_title']."\n" : '';
                $payload .= isset($event_data['changes']['swimlane_name']) ? t('Swimlane:').' '.$event_data['changes']['swimlane_name']."\n" : '';
                $payload .= isset($event_data['changes']['assignee_name']) ? sprintf(t('Assigned to %s', $event_data['changes']['assignee_name']))."\n" : '';
                $payload .= isset($event_data['changes']['date_due']) ? t('Due date:').' '.date('D, d M Y', $event_data['changes']['date_due'])."\n" : '';
                $payload .= isset($event_data['changes']['description']) ? t('Description').': '. $event_data['changes']['description']."\n" : '';
            } else if ($event_name === TaskModel::EVENT_CREATE) {
                // Add details of the new task to the message
                $payload .= isset($event_data['task']['column_title']) ? t('Column:').' '.$event_data['changes']['column_title']."\n" : '';
                $payload .= isset($event_data['task']['swimlane_name']) ? t('Swimlane:').' '.$event_data['changes']['swimlane_name']."\n" : '';
                $payload .= isset($event_data['task']['assignee_name']) ? sprintf(t('Assigned to %s', $event_data['changes']['assignee_name']))."\n" : '';
                $payload .= isset($event_data['task']['date_due']) ? t('Due date:').' '.date('D, d M Y', $event_data['task']['date_due'])."\n" : '';
                $payload .= isset($event_data['task']['description']) ? t('Description').': '.$event_data['task']['description']."\n" : '';
            } else if ($event_name === 'file.create') {
                // Add details of the new file to the message
                $payload .= t('Filename').': '.$event_data['file']['name']."\n";
            } else if (in_array($event_name, array(SubtaskModel::EVENT_UPDATE, SubtaskModel::EVENT_CREATE, SubtaskModel::EVENT_DELETE))) {
                $payload .= isset($event_data['subtask']['title']) ? t('Title:').' '.$event_data['subtask']['title']."\n" : '';
                $payload .= isset($event_data['subtask']['name']) ? sprintf(t('Assigned to %s', $event_data['changes']['assignee_name']))."\n" : '';
                $payload .= isset($event_data['subtask']['time_estimated']) ? t('Time estimated:').' '.$event_data['subtask']['time_estimated']."h\n" : '';
                $payload .= isset($event_data['subtask']['time_spent']) ? t('Time spent:').' '.$event_data['subtask']['time_spent']."h\n" : '';
            }

            if ($this->configModel->get('application_url') !== '') {
                $payload .= $this->helper->url->to('TaskViewController', 'show', array('task_id' => $event_data['task']['id'], 'project_id' => $project['id']), '', true);
            }
        }

        return $payload;
    }

}
