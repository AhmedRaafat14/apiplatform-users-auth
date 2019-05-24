# Simple JWT auth with [API Platform](https://api-platform.com)

### Introduction

As the title says we will create together so simple JWT authentication using [API Platform](https://api-platform.com)
and [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle).
Using of course our lovely [Doctrine User Provider](https://symfony.com/doc/current/security/user_provider.html).

------------------------------------------------------------

### Motivation
There too many tutorials online about symfony with JWT, and also some about the api-platform.
But most of them are really not helpful or they are very short, and most of them that I found was missing many things. They even
don't say that you need to know some concepts first and at the end you find so many confusing developers because of it.

Or they just mentioning the APi Platform name and you enter and nothing mostly it is about symfony. to be honest it make sense as the APi Platform community is too small
and as it is really based on symfony so most of issues already asked and solved by symfony community so we are lucky it is so big community :).

I hope this will be diffrent and if you have any concerns or updates will be much appreciated. Also any questions will try to answer all of them.

------------------------------------------------------------

### Requirements
* PHP >= 7.0 knowledge
* symfony knowledge ([Autowiring](https://symfony.com/doc/current/service_container/autowiring.html), [Dependency Injection](https://symfony.com/doc/current/components/dependency_injection.html))
* Docker knowledge
* REST APIs knowledge
* postgresql knowledge
* Ubuntu or MacOs (Sorry for windows users :)

------------------------------------------------------------

### API Platform installation

The best way for me to install it is using the git repository, or download the [API Platform as .zip file from Github](https://github.com/api-platform/api-platform).

```bash
git clone https://github.com/api-platform/api-platform.git apiplatform-user-auth

cd apiplatform-user-auth
```
##### Make sure nothing on the required ports:
Now, first of all the whole api platform runs on specific ports, so you need to make sure that this ports is free and nothing is listening to it.
* How i can know this ports?
    * You can find them in the docker-compose.yml file in the project root directory, and they always like [80, 81, 8080, 8081, 3000, 5432, 1337, 8443, 8444, 443, 444]
* How to know this?
    * Run this command 
    ```bash
      sudo lsof -nP | grep LISTEN
    ```
* What to do know?
    * Kill any process listening on any of the above ports.
    ```bash
      sudo kill -9 $PROCESS_NUMBER
    ```
------------------------------------------------------------

##### Installation:
* Pull the required packages and everything needed.
```bash
docker-compose pull
```
* Bring the application up and running.
```bash
docker-compose up -d
```   
* You may face some issue here so better to bring everything down and run the command again like this
```bash
  docker-compose down
  COMPOSE_HTTP_TIMEOUT=120 docker-compose up -d
``` 
* Now the application should be running and everything in place:
```bash
docker ps

CONTAINER ID        IMAGE                            COMMAND                  CREATED              STATUS              PORTS                                                                    NAMES
6389d8efb6a0        apiplatform-user-auth_h2-proxy   "nginx -g 'daemon of…"   About a minute ago   Up About a minute   0.0.0.0:443-444->443-444/tcp, 80/tcp, 0.0.0.0:8443-8444->8443-8444/tcp   apiplatform-user-auth_h2-proxy_1_a012bc894b6c
a12ff2759ca4        quay.io/api-platform/varnish     "docker-varnish-entr…"   2 minutes ago        Up 2 minutes        0.0.0.0:8081->80/tcp                                                     apiplatform-user-auth_cache-proxy_1_32d747ba8877
6c1d29d1cbdd        quay.io/api-platform/nginx       "nginx -g 'daemon of…"   2 minutes ago        Up 2 minutes        0.0.0.0:8080->80/tcp                                                     apiplatform-user-auth_api_1_725cd9549081
62f69838dacb        quay.io/api-platform/php         "docker-entrypoint p…"   2 minutes ago        Up 2 minutes        9000/tcp                                                                 apiplatform-user-auth_php_1_cf09d32c3120
381384222af5        dunglas/mercure                  "./mercure"              2 minutes ago        Up 2 minutes        443/tcp, 0.0.0.0:1337->80/tcp                                            apiplatform-user-auth_mercure_1_54363c253a34
783565efb2eb        postgres:10-alpine               "docker-entrypoint.s…"   2 minutes ago        Up 2 minutes        0.0.0.0:5432->5432/tcp                                                   apiplatform-user-auth_db_1_8da243ca2865
1bc8e386bf02        quay.io/api-platform/client      "/bin/sh -c 'yarn st…"   2 minutes ago        Up About a minute   0.0.0.0:80->3000/tcp                                                     apiplatform-user-auth_client_1_1c413b4e4a5e
c22bef7a0b3f        quay.io/api-platform/admin       "/bin/sh -c 'yarn st…"   2 minutes ago        Up About a minute   0.0.0.0:81->3000/tcp                                                     apiplatform-user-auth_admin_1_cfecc5c6b442
```
* Now, if you go to [localhost:8080](http://localhost:8080) you will see their some sample apis listed their the example that comes with the project.

------------------------------------------------------------

### Create the User entity based on [Doctrine User Provider](https://symfony.com/doc/current/security/user_provider.html)
* Install the doctrine maker package to help us make it quick :)
```bash
docker-compose exec php composer require doctrine maker
```
* Create our User entity 
```bash
docker-compose exec php bin/console make:user

 The name of the security user class (e.g. User) [User]:
 > Users

 Do you want to store user data in the database (via Doctrine)? (yes/no) [yes]:
 >

 Enter a property name that will be the unique "display" name for the user (e.g. email, username, uuid) [email]:
 > email

 Will this app need to hash/check user passwords? Choose No if passwords are not needed or will be checked/hashed by some other system (e.g. a single sign-on server).

 Does this app need to hash/check user passwords? (yes/no) [yes]:
 >

The newer Argon2i password hasher requires PHP 7.2, libsodium or paragonie/sodium_compat. Your system DOES support this algorithm.
You should use Argon2i unless your production system will not support it.

 Use Argon2i as your password hasher (bcrypt will be used otherwise)? (yes/no) [yes]:
 >

 created: src/Entity/Users.php
 created: src/Repository/UsersRepository.php
 updated: src/Entity/Users.php
 updated: config/packages/security.yaml


  Success!


 Next Steps:
   - Review your new App\Entity\Users class.
   - Use make:entity to add more fields to your Users entity and then run make:migration.
   - Create a way to authenticate! See https://symfony.com/doc/current/security.html
```

* If you go now to "api/src/Entity" you will find your entity there. If you scroll down a little bit to the getEmail & getPassword functions you will see
something like this which means the this two will be used as the User identifier in the authentication. (Will not use the ROLES in this example it is so simple one)
```php
# api/src/Entity/Users.php

/**
* @see UserInterface
*/
```
- As you know the latest versions of symfony using the [autowiring](https://symfony.com/doc/current/service_container/autowiring.html), feature so also you can see that this entity is already wired and injected with teh repository called "api/src/Repository/UsersReporitory"
```php
# api/src/Entity/Users.php

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 */
class Users implements UserInterface
{
    ...
}
```
------------------------------------------------------------
* You can see clearly in this repository some pre-implemented functions like findbyId(), but now let is create another
function that helps us to create a new user.
    * To add user into the Db will need to define an entity manager like the following:
    ```php
    # api/src/Repository/UsersRepository.php
    class UsersRepository extends ServiceEntityRepository
    {
      /** EntityManager $manager */
      private $manager;
    ....
    }
    ```
    and initialize it in the constructor like the following:
    ```php
    # api/src/Repository/UsersRepository.php
  
    /**
    * UsersRepository constructor.
    * @param RegistryInterface $registry
    */
    public function __construct(RegistryInterface $registry)
    {
      parent::__construct($registry, Users::class);
    
      $this->manager = $registry->getEntityManager();
    }
    ```
    
    * Now, let is create our function like the followings:
    ```php
    # api/src/Repository/UsersRepository.php
  
    /**
     * Create a new user
     * @param $data
     * @return Users
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
    */
    public function createNewUser($data)
    {
        $user = new Users();
        $user->setEmail($data['email'])
            ->setPassword($data['password']);
    
        $this->manager->persist($user);
        $this->manager->flush();
    
        return $user;
    }
    ```
------------------------------------------------------------
* Let us create our controller to consume that repository, will call it "AuthController".
```bash
docker-compose exec php bin/console make:controller

 Choose a name for your controller class (e.g. TinyJellybeanController):
 > AuthController

 created: src/Controller/AuthController.php
 created: templates/auth/index.html.twig


  Success!


 Next: Open your new controller class and add some pages!
```
------------------------------------------------------------
* Now, lets consume this createNewUser function. If you see your controller you will find it is only contains the index function, but we need to create another one will call it "register".
    * We need the UsersRepository right so will create it is object first.
    ```php
    # api/src/Controller/AuthController.php

    use App\Repository\UsersRepository;

    class AuthController extends AbstractController
    {
        /** @var UsersRepository $userRepository */
        private $usersRepository;
    
        /**
         * AuthController Constructor
         *
         * @param UsersRepository $usersRepository
         */
        public function __construct(UsersRepository $usersRepository)
        {
            $this->usersRepository = $usersRepository;
        }
        .......
    }
    ```
    * As you see how this controller will know this repository so will inject it first.
    ```yaml
    # api/config/services.yaml
    
    services:
        ......
      # Repositories
      app.user.repository:
          class: App\Repository\UsersRepository
          arguments:
              - Symfony\Bridge\Doctrine\RegistryInterface
      
      # Controllers
      app.auth.controller:
          class: App\Controller\AuthController
          arguments:
              - '@app.user.repository'
    ```
    * Now, it is our time to implement our new endpoint.
    ```php
    # api/src/Controller/AuthController.php
    
    # Import those
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    
    # Then add this to the class
    /**
     * Register new user
     * @param Request $request
     *
     * @return Response
     */
    public function register(Request $request)
    {
        $newUserData['email']    = $request->get('email');
        $newUserData['password'] = $request->get('password');

        $user = $this->usersRepository->createNewUser($newUserData);

        return new Response(sprintf('User %s successfully created', $user->getUsername()));
    }
    ```
    * Now, we need to let the framework to know about this new api
    ```yaml
    # src/config/routes.yaml
  
    # Register api
    register:
        path: /register
        controller: App\Controller\AuthController::register
        methods: ['POST']
    ```
------------------------------------------------------------
### Testing this new API:
* Make the migration and update the DB first:
```bash
$ docker-compose exec php bin/console make:migration

$ docker-compose exec php bin/console doctrine:migrations:migrate

  WARNING! You are about to execute a database migration that could result in schema changes and data loss. Are you sure you wish to continue? (y/n) y
```
* Now, from Postman or any other client you use. Her am using CURL
```bash
$ curl -X POST -H "Content-Type: application/json" "http://localhost:8080/register?email=test1@mail.com&password=test1"
User test1@mail.com successfully created
``` 
* You want to see this data in the DB:
```bash
$ docker-compose exec db psql -U api-platform api
psql (10.8)
Type "help" for help.

$ api=# select * from users;
 id |     email      | roles | password
----+----------------+-------+----------
  6 | test1@mail.com | []    | test1
(1 row)
```
------------------------------------------------------------
* Oooooh, woow the password is not encrypted what should we do!!!
    * So, as i said before it is exaclty the same as Symfony that is why i said you need to have knowledge about symfony. So will use the Password encoder class.
    ```php
    # api/src/Repository/UsersRepository.php
  
    use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
  
    class UsersRepository extends ServiceEntityRepository
    {
        .......
    
      /** UserPasswordEncoderInterface $encoder */
      private $encoder;
        
      /**
       * UserRepository constructor.
       * @param RegistryInterface $registry
       * @param UserPasswordEncoderInterface $encoder
       */
      public function __construct(RegistryInterface $registry, UserPasswordEncoderInterface $encoder)
      {
          parent::__construct($registry, Users::class);
  
          $this->manager = $registry->getEntityManager();
          $this->encoder = $encoder;
      }
    }
    ```
    * We need to inject it to the repository:
    ```yaml
    # api/config/services.yaml
    
    services:
      .......
      # Repositories
      app.user.repository:
          class: App\Repository\UsersRepository
          arguments:
              - Symfony\Bridge\Doctrine\RegistryInterface
              - Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
    ```
    * Update the create user function:
    ```php
    # api/src/Repository/UsersRepository.php
  
    public function createNewUser($data)
    {
        $user = new Users();
        $user->setEmail($data['email'])
            ->setPassword($this->encoder->encodePassword($user, $data['password']));
        .......
    }
    ```
    * Now, try the register call again, rembmer with diffrent email as we defined the email as Unique:
    ```bash
    $ curl -X POST -H "Content-Type: application/json" "http://localhost:8080/register?email=test2@mail.com&password=test2"
    User test2@mail.com successfully created
    ```
    * check now again:
    ```bash
    $ api=# select * from users;
     id |     email      | roles |                                            password
    ----+----------------+-------+-------------------------------------------------------------------------------------------------
      6 | test1@mail.com | []    | test1
      7 | test2@mail.com | []    | $argon2i$v=19$m=1024,t=2,p=2$VW9tYXEzZHp5U0RMSE5ydA$bo+V1X6rgYZ4ebN/bs1cpz+sf+DQdx3Duu3hvFUII8M
    (2 rows)
    ```
------------------------------------------------------------
#### Install LexikJWTAuthenticationBundle
* Install the bundle and generate the secrets:
```bash
$ docker-compose exec php composer require jwt-auth
```
------------------------------------------------------------
#### Create our authentication
* Before anything if you tried this call for now you will get this result:
```bash
$ curl -X GET -H "Content-Type: application/json" "http://localhost:8080/greetings"
{
    "@context": "/contexts/Greeting",
    "@id": "/greetings",
    "@type": "hydra:Collection",
    "hydra:member": [],
    "hydra:totalItems": 0
}
```
* Let us keep going for now, create a new & very simple API that we will use it in our testing now will call it "/api"
```php
# api/src/Controller/AuthController.php

/**
* api route redirects
* @return Response
*/
public function api()
{
    return new Response(sprintf("Logged in as %s", $this->getUser()->getUsername()));
}
```
* Add it to our Routes
```yaml
# api/config/routes.yaml

api:
    path: /api
    controller: App\Controller\AuthController::api
    methods: ['POST']
```
------------------------------------------------------------
* Now, we need to make some configurations in our security config file:
    * First of all this our provider to our authentication or anything related to users in the application. It is already predefined if you wanna change it you can change the user provider you can do it here.
    ```yaml
    # api/config/packages/security.yaml
    
    app_user_provider:
        entity:
            class: App\Entity\Users
            property: email
    ```
    * Lets make some configs for our "/register" api as we want this api to be public for anyone:
    ```yaml
    # api/config/packages/security
    
    register:
        pattern:  ^/register
        stateless: true
        anonymous: true
    ```
    * Now, let is assume that we need everything generated by the api-platform to not work without JWT token, meaning without authenticated user the api shouldn't return anything.
    So will update the "main" part configs to be like this:
    ```yaml
    # api/config/packages/security.yaml
    
    main:
        anonymous: false
        stateless: true
        provider: app_user_provider
        json_login:
            check_path: /login
            username_path: email
            password_path: password
            success_handler: lexik_jwt_authentication.handler.authentication_success
            failure_handler: lexik_jwt_authentication.handler.authentication_failure
        guard:
            authenticators:
                - lexik_jwt_authentication.jwt_token_authenticator
    ```
    * Also, add some configs for our simple /api.
    ```yaml
    # api/config/packages/security.yaml
  
    api:
        pattern: ^/api
        stateless: true
        anonymous: false
        provider: app_user_provider
        guard:
            authenticators:
                - lexik_jwt_authentication.jwt_token_authenticator
    ```
    * As you see in the above configs we set the anonymous to False we don't want anyone to access this two APIs now, also
    we telling the framework the provider for you is the user provider, and at the end we telling it it is authentication messages
    if it failed or something happened.
    
    * Now, if you tried the call we make it in the beginning  for the /greetings api
    ```bash
    $ curl -X GET -H "Content-Type: application/json" "http://localhost:8080/greetings"
      {
          "code": 401,
          "message": "JWT Token not found"
      }
    ```
    * The same with the simple /api we created:
    ```bash
    $ curl -X POST -H "Content-Type: application/json" "http://localhost:8080/api" 
      {
        "code": 401,
        "message": "JWT Token not found"
      }
    ```
    * As you see it asks you to login :D, there is no token specified so we will creat a very simple API that used by the lexik jwt to authenticate the users,
    and generate their tokens, remember that the login check path should be the same as the check_path under json_login in the security file:
    ```yaml
    # api/config/packages/security.yaml
    ....
    json_login:
            check_path: /login
    
    # api/config/routes.yaml
  
    # Login check to log the user and generate JWT token
    api_login_check:
          path: /login
          methods: ['POST']
    ```
    * Now, Lets try it out and see is it will generate a token for us or what!
    ```bash
    $ curl -X POST -H "Content-Type: application/json" http://localhost:8080/login -d '{"email":"test2@mail.com","password":"test2"}'
      {"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1NTg2OTg4MTIsImV4cCI6MTU1ODcwMjQxMiwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidGVzdDJAbWFpbC5jb20ifQ.nzd5FVhcyrfjYyN8jRgYFp3VOB2QytnPPRGNyp4ZtfLx6IRwg0TWZJPu5OFtOKPkdLO8DQAr_4Fpq_G6oPjzoxmGOASNuRoQonik9FCCq6oAIW3k5utzQecXDVE_ImnfgByc6WYW6a-aWLnsq1qtvxy274ojqdR0rWLePwSWX5K5-t08zDBgavO_87dVpYd0DLwhHIS7F10lNscET7bfWS-ioPDTv-G74OvkcpbcjgwHhXlO7TYubnrES-FsvAw7kezQe4BPxdbXr1w-XBZuqTNEU4MyrBuadSLgjoe_gievNBtkVhKErIkEQZVjeJIQ4xaKaxwmPxZcP9jYkE47myRdbMsL9XHSd0XmGq0bPuGjOJ2KLTmUb5oeuRnY-e9Q_V9BbouEGw0sjw2meo6Jot2MZyv5ZnLci_GwpRtWqmV7ZLw5jNyiLDFXR1rz70NcJh7EXqu9o4nno3oc68zokfDQvGkJJJZMtBrLCK5pKGMh0a1elIz41LRLZvpLYCrOZ2f4wCkGRD_U92iILD6w8EdVWGoO1wTn5Z2k8-GS1-QH9f-4KkOpaYGPCwwdrY7yioSt2oVbEj2FOb1jULteeP_Cpu44HyJktPLPW_wrN2OtZlUFr4Vz_owDSIvNESYk1JBQ_Fjlv9QGmUs9itzaDExjfB4QYoGkvpfNymtw2PI"}
    ```
    As you see it really logged me in and created some token for me so I can use it to call any api in the application.
    If it show some exception like Unable to generate token for the specified configurations, [please check this step here](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#generate-the-ssh-keys-).
    First open you .env file we will need the **JWT_PASSPHRASE** so keep it opened
    ```bash
    $ mkdir -p api/config/jwt
    $ openssl genrsa -out api/config/jwt/private.pem -aes256 4096 # this will ask you for the JWT_PASSPHRASE
    $ openssl rsa -pubout -in api/config/jwt/private.pem -out api/config/jwt/public.pem # will confirm the JWT_PASSPHRASE again
    ```
    
    * Lets try it our to call /api or greetings with this token now:
    ```bash
    $ curl -X GET -H "Content-Type: application/json" -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1NTg2OTg4MTIsImV4cCI6MTU1ODcwMjQxMiwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidGVzdDJAbWFpbC5jb20ifQ.nzd5FVhcyrfjYyN8jRgYFp3VOB2QytnPPRGNyp4ZtfLx6IRwg0TWZJPu5OFtOKPkdLO8DQAr_4Fpq_G6oPjzoxmGOASNuRoQonik9FCCq6oAIW3k5utzQecXDVE_ImnfgByc6WYW6a-aWLnsq1qtvxy274ojqdR0rWLePwSWX5K5-t08zDBgavO_87dVpYd0DLwhHIS7F10lNscET7bfWS-ioPDTv-G74OvkcpbcjgwHhXlO7TYubnrES-FsvAw7kezQe4BPxdbXr1w-XBZuqTNEU4MyrBuadSLgjoe_gievNBtkVhKErIkEQZVjeJIQ4xaKaxwmPxZcP9jYkE47myRdbMsL9XHSd0XmGq0bPuGjOJ2KLTmUb5oeuRnY-e9Q_V9BbouEGw0sjw2meo6Jot2MZyv5ZnLci_GwpRtWqmV7ZLw5jNyiLDFXR1rz70NcJh7EXqu9o4nno3oc68zokfDQvGkJJJZMtBrLCK5pKGMh0a1elIz41LRLZvpLYCrOZ2f4wCkGRD_U92iILD6w8EdVWGoO1wTn5Z2k8-GS1-QH9f-4KkOpaYGPCwwdrY7yioSt2oVbEj2FOb1jULteeP_Cpu44HyJktPLPW_wrN2OtZlUFr4Vz_owDSIvNESYk1JBQ_Fjlv9QGmUs9itzaDExjfB4QYoGkvpfNymtw2PI" "http://localhost:8080/greetings"
    {
        "@context": "/contexts/Greeting",
        "@id": "/greetings",
        "@type": "hydra:Collection",
        "hydra:member": [],
        "hydra:totalItems": 0
    }
    $ curl -X GET -H "Content-Type: application/json" "http://localhost:8080/greetings"
      {
          "code": 401,
          "message": "JWT Token not found"
      }
    ```
    I guess now you see the diffrence.
    
    * what about the /api one let us try it out:
    ```bash
    $ curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1NTg2OTg4MTIsImV4cCI6MTU1ODcwMjQxMiwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidGVzdDJAbWFpbC5jb20ifQ.nzd5FVhcyrfjYyN8jRgYFp3VOB2QytnPPRGNyp4ZtfLx6IRwg0TWZJPu5OFtOKPkdLO8DQAr_4Fpq_G6oPjzoxmGOASNuRoQonik9FCCq6oAIW3k5utzQecXDVE_ImnfgByc6WYW6a-aWLnsq1qtvxy274ojqdR0rWLePwSWX5K5-t08zDBgavO_87dVpYd0DLwhHIS7F10lNscET7bfWS-ioPDTv-G74OvkcpbcjgwHhXlO7TYubnrES-FsvAw7kezQe4BPxdbXr1w-XBZuqTNEU4MyrBuadSLgjoe_gievNBtkVhKErIkEQZVjeJIQ4xaKaxwmPxZcP9jYkE47myRdbMsL9XHSd0XmGq0bPuGjOJ2KLTmUb5oeuRnY-e9Q_V9BbouEGw0sjw2meo6Jot2MZyv5ZnLci_GwpRtWqmV7ZLw5jNyiLDFXR1rz70NcJh7EXqu9o4nno3oc68zokfDQvGkJJJZMtBrLCK5pKGMh0a1elIz41LRLZvpLYCrOZ2f4wCkGRD_U92iILD6w8EdVWGoO1wTn5Z2k8-GS1-QH9f-4KkOpaYGPCwwdrY7yioSt2oVbEj2FOb1jULteeP_Cpu44HyJktPLPW_wrN2OtZlUFr4Vz_owDSIvNESYk1JBQ_Fjlv9QGmUs9itzaDExjfB4QYoGkvpfNymtw2PI" "http://localhost:8080/api"
    Logged in as test2@mail.com
    ```
    As you can see only from the JWT token you can know exactly whos is logged in, and you can improve this by implmenting new User 
    properties like isActive or userRoles .... so on and improve it.

------------------------------------------------------------
##### Thank you for coming so far in this tutorials, I hope that you learned something new.
##### Thank you so much and if you have any questions please don't hesitate to ask.
