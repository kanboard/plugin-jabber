<?php

namespace Kanboard\Plugin\Jabber\Notification;

use Exception;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Protocol\Message;
use Fabiang\Xmpp\Protocol\Presence;
use Kanboard\Core\Base;
use Kanboard\Notification\NotificationInterface;

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
            $jid = $this->userMetadata->get($user['id'], 'jabber_jid');

            if (! empty($jid)) {
                $project = $this->project->getById($event_data['task']['project_id']);
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
            $room = $this->projectMetadata->get($project['id'], 'jabber_room');

            if (! empty($room)) {
                $client = $this->getClient();

                $channel = new Presence;
                $channel->setTo($room)->setNickName($this->config->get('jabber_nickname'));
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
        $options = new Options($this->config->get('jabber_server'));
        $options->setUsername($this->config->get('jabber_username'));
        $options->setPassword($this->config->get('jabber_password'));
        $options->setTo($this->config->get('jabber_domain'));
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
     */
    public function getMessage(array $project, $event_name, array $event_data)
    {
        if ($this->userSession->isLogged()) {
            $author = $this->helper->user->getFullname();
            $title = $this->notification->getTitleWithAuthor($author, $event_name, $event_data);
        } else {
            $title = $this->notification->getTitleWithoutAuthor($event_name, $event_data);
        }

        $payload = '['.$project['name'].'] ';
        $payload .= $title;
        $payload .= ' '.$event_data['task']['title'];

        if ($this->config->get('application_url') !== '') {
            $payload .= ' '.$this->helper->url->to('task', 'show', array('task_id' => $event_data['task']['id'], 'project_id' => $project['id']), '', true);
        }

        return $payload;
    }
}
