create table war_assignment(
	war_id int not null,
	player_id int not null,
	assigned_player_id int not null,
	message varchar(255) not null,
	date_created datetime not null,
	date_modified datetime default null,
	primary key(war_id, player_id, assigned_player_id),
	foreign key(war_id, player_id) references war_player(war_id, player_id) on delete cascade,
	foreign key(war_id, assigned_player_id) references war_player(war_id, player_id) on delete cascade
);

