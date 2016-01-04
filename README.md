## Simple MVC and WebSocket

* Author: [Nguyễn Thế Thuận](https://github.com/truvu) [Vietnamese]
* Email: truvu.vietnam@gmail.com
* FB: [Trụ Vũ](https://www.facebook.com/www.univeser.vn)
* Demo (chat): https://mvc-nguyenthuan.c9users.io

#### Router

```php
<?php
    use TruVu\Mvc\Application;
    use TruVu\Loader;
    
    $loader = new Loader;
    
    $app = new Application();
    $app->get('/', function(){
        echo 'Index Page using callback function';
    });
    $app->get('/post/(^[0-9]+$)', function($id){
        echo "Index Using Callback Function: postID = $id";
    });
    $app->handle(); // use it if you want to use controller;
    
    // http://localhost/account/login:
    // in file: app\mvc\controllers\account.php
    class AccountController extends TruVu\Mvc\Controller
    {
        public function login(){
            $this->view->title = 'Account Login';
            $this->view->render('account/login');
            // or $this->view->render(['account/header', 'account/login', 'account/footer']);
        }
     }
?>
```

#### Database - PDO, MongoDB, File System (test);

```php
<?php
    use TruVu\DI;
    use TruVu\Loader;
    use TruVu\Db\Mysql as DBMysql;
    use TruVu\Mvc\Application;
    
    $loader = new Loader;
    $loader->register(__DIR__.'/../app/mvc/models/'); // Your models dir or other
    
    $di = new DI;
    $di->set('mysql', function(){
        return DBMysql::connect(
            array(
                'host'      => 'localhost',
                'username'  => 'root',
                'password'  => '',
                'dbname'    => 'test'
            )
        );
    });
    
    $app = new Application($di);
    
    $app->get('/users/profile/(^[0-9]{1,11}$)', function($id) use($app){
        // option 1:
        $user = $app->mysql->query('SELECT * FROM user WHERE id=:id', ['id'=>$id])->fetch();
        
        // option 2:
        $user = User::findFirst($id);
        
        echo json_encode($user);
    });
    
    // model:
    class User extends TruVu\Db\Mysql
    {
        public $id;
        public $name;
    }
?>
```

##### Mysql

```php
<?php
    // SELECT:
    $users = User::find(
        'columns' => 'name, email',
        'where' => 'id=:id',
        'bind' => ['id'=>1]
    );
    
    // JOIN:
    $users = User::find(
        'columns' => 'a.id, a.name, p.id as pid, p.title, p.content', // 'a' is default;
        'join' => array('post as p' => 'a.id = p.user'),
        'where' => 'p.user = :user',
        'bind' => ['user' => 1],
        'limit' => 10
    );
    
    // INSERT:
    $user = new User;
    $user->name = 'User Name';
    $user->email = 'example@email.com';
    $user->save();
    echo 'Insert id = '.$user->id;
    
    // UPDATE:
    $user = new User(1);
    $user->name = 'New Name';
    $user->save();
    
    // DELETE
    $user = new User(1);
    $user->delete();
?>
```

##### MongoDB

```php
    <?php
        // register in DI
        $di->set('mongo', function(){
            return DBMongo::connect(
                array(
                    'host' => 'localhost',
                    'dbname' => 'test'
                )
            );
        });
    
        // model
        class Comment extends TruVu\Db\Mongo
        {
        }
        
        // find
        Comment::findFirst(
            array('_id' => 1)
        );
        
        // Update
        Comment::update(array('_id'=>1), array('$set'=>array('content'=>'new content')));
        
        // delete
        Comment::delete(array('_id'=>1));
    ?>
```

##### File

```php
<?php
    // register in DI
    $di->set('file', function(){
        return DBFile::connect('path/to/database/data');
    });
    
    // file database: path/to/database/data/Comment/
    // model:
    class Comment extends TruVu\Db\File
    {
    }
    
    // find first
    Comment::findFirst(
        array(
            'file'=>1, // it is post id or file name
            'id' => 10
        )
    );
    
    // find all
    Comment::find(
        array('file' => 1),
        array('limit'=>10)
    );
    
    // insert for post id = 1
    $comment = new Comment(1);
    $comment->id = round(microtime(true)*1000);
    $comment->user = 1;
    $comment->content = 'Comment content';
    $comment->save();
    
    // update for post id = 1 and comment id = 1
    $comment = new Comment(1);
    $comment->content = 'New content';
    $comment->save(1);
    
    // delete:
    $comment = new Comment(1);
    // delete all comment of post => delete file post:
    $comment->delete();
    // delete a comment id = 1;
    $comment->delete(1);
?>
```

#### HTTP

##### Request
```php
<?php
    // list action for request: get, post, query, isPost(), isGet(), isAjax(), getHeader();
    // I am sorry for have not build request file.
    
    $app->get('/product/list', function() use($app){
        $time = $app->request->get('time', 'number', time());
        // this mean: $time =  empty($_GET['time'])?time():$_GET['time'];
    });
?>
```

##### Response
```php
<?php
    // list action: 
    Response::setHeader('Header-Name', 'value'); 
    Response::json($array); // convert to json
    Response::content($data); // convert to string
    Response::redirect('/url/');
?>
```

##### COOKIE, SESSION
```php
<?php
    // list: has('name'), set('name', 'value', $days, $path), get('name'), delete('name');
?>
```

### WebSocket
##### Server PHP
```php
<?php
    // command line in:  path\to\project\app\socket
    // press code: php -q index.php

    require __DIR__.'/../config/define.php';
    use Truvu\Loader;
    use Truvu\DI;
    use Truvu\Lib\Socket;

    $loader = new Loader;
    $loader->register(CORE.'lib/');

    Socket::listen(3000, 'localhost');

    Socket::connect(function($data){
        Socket::setData($data);

        Socket::on('chat', function($data){
            Socket::emit('chat', $data);
        });
        Socket::on('time', function($data){
            Socket::emit('time', $data);
        });
    });
?>
```

##### client javascript
```javascript
// connect websocket
var socket = Socket.connect('http://localhost:3000/');

// send data to server
var show = setInterval(function(){
    socket.emit('time', Date.now());
}, 5000);
socket.emit('chat', {name: 'Nguyen The Thuan', text: 'Hello world!'});

// Listen event from server
socket.listen(function(){
    socket.on('chat', function (object){
        chat.innerHTML = chat.innerHTML+('<b>'+object.name+'</b>: '+object.text+'<br/>');
    });
    socket.on('time', function (number){
        time.innerHTML = 'this time now is '+number;
    });
});
```

### Asset: css, javascript

* workspace in: \project\app\asset\
* manager for: compress, when test project => define in: 'app\config.define.php';
* with JavaScript: multi files write to one file only, set list in: 'app\asset\js\list.php', with key of array is the task in view such as '$this->view->js('index')';
* in view file:
```html
<!DOCTYPE HTML>
<html>
    <header>
    <title> <?php echo $title; ?> </title>
    <?php $this->asset->css('index')->js('index'); ?>
    <!-- <link rel="stylesheet" href="http://localhost/asset/css/index.css"/> -->
    </header>
    <body>
    </body>
 </html>
```

### JavaScript: DomJS

##### Create dom
```javascript
var Div = Dom.createClass({
    state: {name: 'Nguyễn Thế Thuận'},
    changeName: function(e){
        Div.setState({name: this.value});
    },
    buttonClick: function(e){
        console.log(Div.ref.btn);
    },
    render: function(){
        return Dom.node(
            '<div/>,
            null,
            Dom.node('<div>My name is {this.state.name}</div>'),
            Dom.node(
                '<input type="text" placeholder="Input name"/>', 
                {keyup: this.changeName}
            ),
            Dom.node(
                '<input type="button" value="Submit" ref="btn"/>',
                {click: this.buttonClick}
            )
        );
    }
});
Dom.render(document.getElementById('content'), Div);
```