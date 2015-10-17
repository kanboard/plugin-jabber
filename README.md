Jabber/XMPP plugin for Kanboard
===============================

Receive Kanboard notifications on Jabber.

Author
------

- Frederic Guillot
- License MIT

Installation
------------

- Create a folder **plugins/Jabber**
- Copy all files under this directory

Configuration
-------------

### XMPP Server Settings

Go to **Settings > Integrations > Jabber** and fill the form:

- **XMPP server address**: Address of your Jabber server (tcp://jabber.example.com:5222)
- **Jabber domain**: Jabber domain
- **Username**: Kanboard username to connect to your Jabber server
- **Password**: Kanboard password to connect to your Jabber server
- **Jabber nickname for Kanboard**: nickname used by Kanboard

### Receive individual user notifications

- Go to your user profile then choose **Integrations > Jabber**
- Enter your Jabber Id (JID), by example me@example.com
- Then enable Jabber notifications in your profile: **Notifications > Select Jabber**

### Receive project notifications to a room

- Go to the project settings then choose **Integrations > Jabber**
- Enter the name of the room, by example myproject@conference.example.com

## Troubleshooting

- Enable the debug mode
- All connection errors with the XMPP server are recorded in the log files `data/debug.log` or syslog
