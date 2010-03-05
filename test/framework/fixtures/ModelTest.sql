# ModelTest
create table belongsto(
    id int not null primary key auto_increment,
    hasmany_id int,
    name varchar(255),
    dt datetime
);

insert into belongsto values(null, 1, 'a', '2010-05-19');
insert into belongsto values(null, 1, 'b', '2009-05-19');

create table hasmany(
    id int not null primary key auto_increment
);

insert into hasmany values(null);
    
