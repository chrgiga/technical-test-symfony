Technical Test Symfony
======================

------------- Installation -----------

== Requirements ==

· PHP 7.1 or higher
· Composer

== Project installation ==
To install the project, you must clone the code repository:

    git clone https://github.com/chrgiga/technical-test-symfony.git

Set database credentials data on environment file:
    create .env.local file (.env.local or what you needed) and add follow data:
        DATABASE_URL=mysql://[dbUser]:[dbUserPassword]@db:[dbPort]/[dbName]?serverVersion=[serverVersion]
        ex:
        DATABASE_URL=mysql://root:root@db:3306/symfony?serverVersion=8.0.17

== Application initialisation ==
Go to project dir and install vendor libraries:

    cd project/
    composer install


Grant read, write permissions for cache and logs for each environment (inside the project root):

    chmod 777 -R var/cache
    chmod 777 -R var/logs


Create the database:

    php bin/console doctrine:database:create


Build the database schema:

    php bin/console doctrine:schema:create


The application is almost installed!

== Populating database ==
Load data fixtures on database:

    php bin/console doctrine:fixtures:load


== Associating media file to an event and user ==
To associate a file with an event and user you must execute the event:add-media command:

    php bin/console event:add-media [the user id] [the event id] [the media uri (can be public path from project or external url]
    ex.1:
    php bin/console event:add-media 2 23 https://image.shutterstock.com/image-photo/beautiful-water-drop-on-dandelion-260nw-789676552.jpg
    ex.2:
    php bin/console event:add-media 2 23 /var/wwww/project/public/images/image_file.jpg


------------- Default users data -----------
The fixtures populates database with random users, but there are 3 known users:

User 1:
    email: default_user@gmail.com
    role: DEFAULT_USER
User 2:
    email: group_admin@gmail.com
    role: ROLE_GROUP_ADMIN
User 3:
    email: app_admin@gmail.com
    role: ROLE_APP_ADMIN

All the users has the same password: 123456

------------- URLS -----------
Login: /login
Logout: /logout
Events: /events
Selected event: /events/{eventId}