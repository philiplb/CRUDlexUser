CRUDlex User
============

CRUDlexUser is a library offering an user provider for symfony/security.

## API Documentation

The CRUDlexUser API itself is documented here:

* [0.9.0](http://philiplb.github.io/CRUDlexUser/docs/api/0.9.0/)

## Usage

This library offers two parts. First, a management interface for your admin panel to
perform CRUD operations on your userbase and second, an symfony/security UserProvider
offering in order to connect the users with the application.

### The Admin Panel

The admin panel for your users is based on [CRUDlex](https://github.com/philiplb/CRUDlex). So all you have
to do is to add the needed entities to your crud.yml:

#### Users

```yml
user:
    label: User
    table: user
    fields:
        username:
            type: text
            label: Username
            required: true
        password:
            type: text
            label: Password Hash
            description: 'Set this to your desired password. Will be automatically converted to an hash value not meant to be readable.'
            required: true
        salt:
            type: text
            label: Password Salt
            description: 'Auto populated field on user creation. Used internally.'
            required: false
```

Plus any more fields you need.

#### Roles

```yml
role:
    label: Roles
    table: role
    fields:
        role:
            type: text
            label: Role
            required: true
```

#### Connecting Users and Roles

```yml
userRole:
    label: User Roles
    table: userRole
    fields:
        user:
            type: reference
            label: User
            reference:
                table: user
                nameField: username
                entity: user
            required: true
        role:
            type: reference
            label: Role
            reference:
                table: role
                nameField: role
                entity: role
            required: true
```

In order to get the salt generated and the password hashed, you have to let the
library add some CRUDlex events in your initialization:

```PHP
$crudUserSetup = new CRUDlex\CRUDUserSetup();
$crudUserSetup->addEvents($app['crud']->getData('user'));
```

### The UserProvider

Simply instantiate and add it to your symfony/security configuration:

```PHP
$userProvider = new CRUDlex\CRUDUserProvider($app['crud']->getData('user'), $app['crud']->getData('userRole'));
 $app->register(new Silex\Provider\SecurityServiceProvider(), array(
     'security.firewalls' => array(
         'admin' => array(
             //...
             'users' => $userProvider
         ),
     ),
 ));
```

## Build Status

[![Build Status](https://travis-ci.org/philiplb/CRUDlexUser.svg?branch=master)](https://travis-ci.org/philiplb/CRUDlexUser)
[![Coverage Status](https://coveralls.io/repos/philiplb/CRUDlexUser/badge.png?branch=master)](https://coveralls.io/r/philiplb/CRUDlexUser?branch=master)

## Misc

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/dd63ce7f-349f-42dd-8e71-076950b726e5/mini.png)](https://insight.sensiolabs.com/projects/dd63ce7f-349f-42dd-8e71-076950b726e5)
