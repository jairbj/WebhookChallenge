# HOOTSUITE Challenge #

## Challenge ##
[challenge.md]()


## What I did ##
I made a small project to simulate a webhook that receives messages and forward to previously defined destinations.
It was developed in PHP/Symfony3.
The project was made based in REST concepts and it answers the GET, POST, DELETE, PUT and PATCH requisitions.
The endpoints where tested using PHP Unit. 
All requisitions to the webhook service are answered and should be made in JSON format.

## Considerations about security ##
Due to the short time, I didn’t implement neither validation to requests nor to sign the messages sent. Either I didn’t require HTTPS urls but it should be mandatory in a production environment.
Of course, in a production environment I’d use or RSA or ECC signature. I wouldn’t like to use HMAC as it’s use symmetric keys we need another step to ensure the keys are transferred in a secured way to the other side of endpoint.
Another option is authenticating requests to webhook based in credentials using JSON Web Tokens. The token can be transferred both in a “plain” way over HTTPS or either over a OAUTH layer of security.
The service also verifies if a valid URL was given and it won’t send messages to URLs that resolves to a private address.

## Considerations about scalability ##
Instead this project that relies in a centralized MySql the server can’t handle millions of connections, but it was developed with scalability in mind.
When a message is posted to the webhook it is added to a queue based on the destination.
The project has a worker module that consumes and process these messages and you can run multiple workers, each one processing one queue (one destination). The message ordering for each destination is guaranteed. You can also run a single worker to process all queues.
Each work can run in an independent server but it needs to connect to the same database server.
As it’s a small project (proof of concept only) I didn’t added validation to ensure you started only one worker for each queue. If you add more than one, the message ordering will not be guaranteed. 
In another word, this project can scale as soon you have multiple destinations.
In a production environment, I’d use probably a queue server like RabbitMQ.

## First ##
Download composer and install the required dependencies with

    php composer.phar install

## Instructions to run the server ##
1. Configure the database definitions in `./app/config/parameters.yml`
2. Create the database using the command `php ./bin/console doctrine:database:create`
3. Create database schema using the command `php ./bin/console  doctrine:migrations:migrate`
4. Run the Symfony built In server using the command `php ./bin/console server:run`
The server will start listening in `http://127.0.0.1:8000` 

## PHPUnit ##
There are PHPUnit tests in the `./tests` folder. 
You can run those with the command: `./vendor/bin/phpunit`
Attention: Run the tests will erase the database.

## Webhook Requisitions (documentation) ##
The webhook documentation are located in both `doc.md` and `doc.html` files. 
After start the server you can also read the documentation in `http://127.0.0.1:8000/doc`. 
**All requests and responses should be made in JSON format.**

## Starting the message processor ##
The message processor can be started with the command

    php ./bin/console message-processor
      
The service will start, process the messages and exit.
### Options: ###

    --persistent        
If set, the service will run in a persistent mode, it won’t exit until it’s cancelled.
    
    --destination=DESTINATION
Indicates that the service should only process messages from the DESTINATION (destination id) queue. If this option isn’t set, the service will process messages from all queues.
    
    --retry=RETRY
Indicates how many times (RETRY) the service will retry to deliver a message (in case of error) before removes it. Default = 3.

    --retry-delay=RETRY-DELAY
Indicates how many time, in seconds (RETRY-DELAY), the service should wait before retry to deliver a message (in case of error). Default = 1.

**Attention:** The message processor will remove automatically messages that aren’t delivered for more than 24h. 

## Extra information ##
When you run the Symfony built in server, it automatically set environment as DEVELOPMENT, so in case of error it’ll return the full debug stack to the client. It doesn’t happen if environment is set to PRODUCTION.
As I didn’t use a queue server I decided to add GET and DELETE method to the “messages” endpoint, so we can check and eventually remove messages from the queue. The messages will remain in the queue only before they are processed.
If you remove a destination, it’ll automatically removes all the messages to this destination that hasn’t been already processed.
I really love backend programming and I really would like to be part of HootSuite team.
For me programming isn’t a job, is a pleasure.


