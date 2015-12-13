# phalcon-rest-boilerplate
Phalcon boilerplate application that provides a RESTful API using MongoDB.

* HTTP Methods
    * GET
    * POST
    * PUT
    * DELETE
    * OPTIONS
* MongoDB
    * Uses MongoDB with SSL support
* Security
    * Whitelist IP address validation
    * Token validation
* CRUD (Create, Read, Update and Delete)
    * Provides sample for CRUD actions on a Model
* Logging
    * Provides application logging

## Table of Contents
+ [Get Started](#get-started)
+ [REST API Reference](#rest-api-reference)
+ [Release Notes](#release-notes)

## <a name="get-started"></a>Get Started

On my local machine I have the following installed.
* Apache 2.4.10
* PHP 5.5.x
* Phalcon 2.0.x
* MongoDB 3.0.x
* [PHP MongoDB Database Driver](http://pecl.php.net/package/mongo)

### Apache
Here is sample configuration to get the API running on port 10060.

    Listen 10060
    <VirtualHost *:10060>
        ServerName localhost:10060
        ServerAdmin emad.najeeb@gmail.com
        DocumentRoot "/Library/WebServer/Documents/phalcon-rest-boilerplate/"
        DirectoryIndex "index.php"
        <Directory "/Library/WebServer/Documents/phalcon-rest-boilerplate/">
            Options +Indexes +FollowSymLinks +MultiViews +Includes
            AllowOverride All
            Require all granted
        </Directory>
        <IfModule mod_ssl.c> 
            SSLEngine On 
            SSLProxyEngine On
            SSLProtocol all -SSLv2 -SSLv3
            SSLCipherSuite ALL:!aNULL:!ADH:!eNULL:!LOW:!EXP:RC4+RSA:+HIGH:+MEDIUM
            
            SSLCertificateFile "/Users/emadnajeeb/Documents/ssl-certificates/local/server.crt"
            SSLCertificateKeyFile "/Users/emadnajeeb/Documents/ssl-certificates/local/server.key"
        </IfModule> 
    </VirtualHost>

### Phalcon Framework
I assume you already have Phalcon framework installed. If not, then follow instructions at [Phalcon Website](http://phalconphp.com).

### MongoDB
This boilerplate application uses MongoDB. Use the installation instructions on [MongoDB Site](https://docs.mongodb.org/manual/installation/). You can ofcouse use any database of your choice. 

For Mac OS X, I used Homebrew.

    brew update
    brew install mongodb --with-openssl

### Application Supporting folders

Create the following supporting folders
```
cd phalcon-rest-boilerplate
mkdir logs
touch logs/app.log
chmod -R 777 logs
```

## <a name="rest-api-reference"></a>REST API Reference

Here are the available REST API operations:
+ [Identity](#api-identity)
+ [User](#api-user)

### <a name="api-identity"></a>Identity namespace
+ GET /v1/identity/token
+ OPTIONS /v1/identity/token

#### Get Token
##### Sample Request
```
curl -k -v -X GET https://localhost:10060/v1/identity/token \
-H "Content-Type:application/json"
```
##### Sample Response
```
{
  "status": "success",
  "data": "jUd6jyPb1l3iJ3MUN3Z6Br8oiEEPq4A",
  "error": null
}
```
### <a name="api-user"></a>User namespace

+ POST /v1/user
+ GET /v1/user/<id>
+ PUT /v1/user/<id>
+ DELETE /v1/user/<id>
+ OPTIONS /v1/user

#### Create user
##### Sample Request
```
curl -k -v -X POST https://localhost:10060/v1/user \
-H "Content-Type:application/json" \
-H "X-CSRFToken:dDw1yRYL3G09XW6fQ0qBnMhSanyAstnG" \
-d '{
    "name": "User Name"
}'
```
##### Sample Response
HTTP/1.1 201 Created
```
{
  "status": "success",
  "data": "{\"_id\":{\"$id\":\"56650acbbf664d2f78d63af1\"},\"name\":\"User Name\",\"createdDate\":{\"sec\":1449462475,\"usec\":911000},\"modifiedDate\":{\"sec\":1449462475,\"usec\":911000}}",
  "error": null
}
```

#### List user
##### Sample Request
```
curl -k -v -X GET https://localhost:10060/v1/user/566dab2cbf664dae7ad63af2 \
-H "Content-Type:application/json" \
-H "X-CSRFToken:dDw1yRYL3G09XW6fQ0qBnMhSanyAstnG"
```
##### Sample Response
HTTP/1.1 200 OK
```
{
  "status": "success",
  "data": "{\"_id\":{\"$id\":\"566dab2cbf664dae7ad63af2\"},\"name\":\"User Name\",\"createdDate\":{\"sec\":1449462475,\"usec\":911000},\"modifiedDate\":{\"sec\":1449462475,\"usec\":911000}}",
  "error": null
}
```

#### Update user
##### Sample Request
```
curl -k -v -X PUT https://localhost:10060/v1/user/566dab2cbf664dae7ad63af2 \
-H "Content-Type:application/json" \
-H "X-CSRFToken:dDw1yRYL3G09XW6fQ0qBnMhSanyAstnG" \
-d '{
    "name": "User Name update"
}'
```
##### Sample Response
HTTP/1.1 200 OK
```
{
  "status": "success",
  "data": "{\"_id\":{\"$id\":\"566dab2cbf664dae7ad63af2\"},\"name\":\"User Name update 1\",\"createdDate\":{\"sec\":1449462475,\"usec\":911000},\"modifiedDate\":{\"sec\":1449507337,\"usec\":226000}}",
  "error": null
}
```

#### Delete user
##### Sample Request
```
curl -k -v -X DELETE https://localhost:10060/v1/user/566dab2cbf664dae7ad63af2 \
-H "Content-Type:application/json" \
-H "X-CSRFToken:dDw1yRYL3G09XW6fQ0qBnMhSanyAstnG"
```
##### Sample Response
HTTP/1.1 200 OK
```
{
  "status": "success",
  "data": null,
  "error": null
}
```

## <a name="release-notes"></a>Release Notes
### Version 1.0.0 (Date: Dec 13, 2015)

* Initial release
