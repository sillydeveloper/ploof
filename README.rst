PHP Lightweight Object Oriented Framework (aka ploof) : Quickstart!
-------------------------------------------------------------------

    Ploof Development Team: Andrew Ettinger, Nick Sinopoli, Steven Carnegie and Hang Dao. 
    
    Special thanks to AirAdvice for their contributions.
    
    Copyright (c) 2010, ploof development team
    All rights reserved.
    
    Redistribution and use in source and binary forms, with or without modification, are permitted provided 
    that the following conditions are met:
    
    Redistributions of source code must retain the above copyright notice, this list of conditions and the 
    following disclaimer. 
    Redistributions in binary form must reproduce the above copyright notice, this list of 
    conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    The names of its contributors may not be used to endorse or promote products derived from this software without 
    specific prior written permission.
    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
    ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
    INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
    GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
    LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
    OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
    
Get the code
~~~~~~~~~~~~

We don't have a release yet (we're unit testing the new database repository framework), so to get the code, clone us::

    git clone git://github.com/sillydeveloper/ploof.git

Creating Models
~~~~~~~~~~~~~~~

- Clone the code then initialize ploof for a first run::

    ./ploof install
    
- Edit config/config.application.php to connect to your database. We have several plugins available and are looking to add more.
    
- ploof is set up to automatically build your model files based on your database, wiring in all the relationships for you. So create a simple migration by running::

    ./ploof migration
    
- Open resource/migrations/trunk/1__Migration and add this (for mysql)::

    create table User(id int not null primary key auto_increment, username varchar(255), password varchar(255));
    create table Post(id int not null primary key auto_increment, User_id int, title varchar(255), body text);
            
- Update your database via::

    ./ploof migrate
    
- Now we can generate our models::

    ./ploof generate_models
    
You should have User and Post objects available in the model folder. You can use them to do some associative things right out of the box::

    $new_user= new User();
    $new_user->username= 'foovius';
    $new_user->store();
    $u= User::find_object(array('username'=>'foovius'));
    $p= new Post();
    $p->title= 'Why hello there';
    $u->Post->add_object($p);
    $all_foovius_posts= $u->Post->find();

Controllers and Views
~~~~~~~~~~~~~~~~~~~~~

Let's create a controller. Create a new file called Posts.php in the controller folder with::

    <?
        class Posts extends ApplicationController
        {
            function save()
            {
                $post= new Post();
                $post->populate_from($this->data['Post']);
                $post->store();
            }

            function index()
            {
                $this->assign('posts', Post::find());
            }
            
            static function classname()
            {
                return __CLASS__;
            }
        }
    ?>

In the views folder, create a folder called Posts and add a file index.html to it::

    Welcome to posts index!
    <?=core\Form::start('/Posts/save')?>
        Make a new post: <?=core\Form::text(new Post(), 'title')?> <input type='submit'/>
    <?=core\Form::end()?>
    <div>
        <? foreach($posts as $post) { ?><div><?=$post->title?></div><? } ?>
    </div>

Server Setup 
~~~~~~~~~~~~

Ok, but how do I load this up in a webserver? Point your favorite webserver to the public folder, and turn on rewrites with the following (apache style)::

    RewriteRule   ^/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/?$ /index.php?controller=$1&action=$2 [L,QSA]                          
    RewriteRule   ^/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)?$ /index.php?controller=$1&action=$2&id=$3 [L,QSA]
    RewriteRule   ^/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)?$ /index.php?parent=$1&parentid=$2&controller=$3&action=$4 [L,QSA]
    RewriteRule   ^/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)?$ /index.php?parent=$1&parentid=$2&controller=$3&action=$4&id=$5 [L,QSA]
    RewriteRule   ^/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)/([\_A-Za-z0-9-]+)?$ /index.php?controller=$1&action=$2&id=$3&subcontroller=$4&subaction=$5&subid=$6 [L,QSA]
    RewriteRule   ^/([\_A-Za-z0-9-]+)/?$ /index.php?controller=$1 [L,QSA]
    
You should now be able to point your browser at::

    http://[domain]/Posts

Change the Layout
~~~~~~~~~~~~~~~~~

In view/layout/default.html you can redesign your layout for each page. 

Testing, Testing, Testing
~~~~~~~~~~~~~~~~~~~~~~~~~

You can run our framework testing via::

    ./ploof test framework
    




