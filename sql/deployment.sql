-- Deployment tracking database tables
-- CREATE a database if you dont have one and name it it490_gamelib;
-- use it490_gamelib and then run this sql.

create table if not exists deployment_packages (
    packageID int not null auto_increment,
    packageName varchar(255) not null,
    version float not null,
    status varchar(50) not null default 'NEW',
    created_by varchar(100) not null,
    created_at timestamp not null default current_timestamp,
    primary key (packageID)
);

create table if not exists deployment_history (
    id int not null auto_increment,
    package_id int not null,
    status varchar(50) not null,
    comments text,
    created_at timestamp not null default current_timestamp,
    primary key (id),
    foreign key (package_id) references deployment_packages(packageID)
);