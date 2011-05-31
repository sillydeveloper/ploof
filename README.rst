PHP LIGHTWEIGHT OBJECT ORIENTED FRAMEWORK README

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

We don't have a release yet, so cloning our repository is the only way to get the code::
    git clone git://github.com/sillydeveloper/ploof.git

Models and ORM
~~~~~~~~~~~~~~

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
    
You should have User and Post objects available in the model folder. You can use them to do magical associative things right out of the box::

    $new_user= new User();
    $new_user->username= 'foovius';
    $new_user->store();
    $u= User::find(array('username'=>'foovius'));
    $p= new Post();
    $p->title= 'Why hello there';
    $u->Post->add_object($p);
    $all_foovius_posts= $u->Post->find();

Controllers and Views
~~~~~~~~~~~~~~~~~~~~~

Testing, Testing, Testing
~~~~~~~~~~~~~~~~~~~~~~~~~





