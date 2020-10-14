create table tbl_friend_request
(
    id bigint unsigned auto_increment primary key ,
    your_id bigint unsigned not null ,
    requested_id bigint unsigned not null ,
    created_at timestamp null,
    updated_at timestamp null,
    requested_status smallint default 0 comment 'pending, accepted, rejected',
    foreign key (your_id) references users(id) on delete restrict on update cascade ,
    foreign key (requested_id) references users(id) on delete restrict on update cascade ,
    unique (your_id, requested_id)
);

create table tbl_friend_list
(
    my_id bigint unsigned not null ,
    friends_id bigint unsigned not null ,
    created_at timestamp null,
    updated_at timestamp null,
    is_blocked boolean default false,
    foreign key (my_id) references users(id) on delete restrict on update cascade ,
    foreign key (friends_id) references users(id) on delete restrict on UPDATE cascade ,
    unique (my_id, friends_id)
);

alter table users
    add active_profile_image varchar(200) null;

alter table users
    add username varchar(30) null;

create unique index users_username_uindex
    on users (username);


