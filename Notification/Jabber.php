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
     * @param  string    $eventName
     * @param  array     $eventData
     */
    public function notifyUser(array $user, $eventName, array $eventData)
    {
        try {
            $jid = $this->userMetadataModel->get($user['id'], 'jabber_jid');

            if (! empty($jid)) {
                if ($eventName === TaskModel::EVENT_OVERDUE) {
                    foreach ($eventData['tasks'] as $task) {
                        $eventData['task'] = $task;
                        $this->sendDirectMessage($jid, $eventName, $eventData);
                    }
                } else {
                    $this->sendDirectMessage($jid, $eventName, $eventData);
                }
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
     * @param  string    $eventName
     * @param  array     $eventData
     */
    public function notifyProject(array $project, $eventName, array $eventData)
    {
        try {
            $room = $this->projectMetadataModel->get($project['id'], 'jabber_room');

            if (! empty($room)) {
                $this->sendGroupMessage($project, $room, $eventName, $eventData);
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
        $payload .= $title;
        $payload .= ' '.$event_data['task']['title'];

        if ($this->configModel->get('application_url') !== '') {
            $payload .= ' '.$this->helper->url->to('TaskViewController', 'show', array('task_id' => $event_data['task']['id'], 'project_id' => $project['id']), '', true);
        }

        return $payload;
    }

    /**
     * Send XMPP message to someone
     *
     * @param string $jid
     * @param string $eventName
     * @param array  $eventData
     */
    public function sendDirectMessage($jid, $eventName, $eventData)
    {
        $project = $this->projectModel->getById($eventData['task']['project_id']);
        $client = $this->getClient();

        $message = new Message();
        $message->setMessage($this->getMessage($project, $eventName, $eventData))
            ->setTo($jid);

        $client->send($message);
        $client->disconnect();
    }

    /**
     * Send XMPP GroupChat message
     *
     * @param array  $project
     * @param string $room
     * @param string $eventName
     * @param array  $eventData
     */
    public function sendGroupMessage(array $project, $room, $eventName, array $eventData)
    {
        $client = $this->getClient();

        $channel = new Presence();
        $channel->setTo($room)->setNickname($this->configModel->get('jabber_nickname'));
        $client->send($channel);

        $message = new Message();
        $message->setMessage($this->getMessage($project, $eventName, $eventData))
            ->setTo($room)
            ->setType(Message::TYPE_GROUPCHAT);

        $client->send($message);
        $client->disconnect();
    }
}
