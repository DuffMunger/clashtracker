create table war_assignment(
	war_id int not null,
	player_id int not null,
	assigned_player_id int not null,
	message varchar(1023),
	date_created datetime not null,
	date_modified datetime default null,
	primary key(war_id, player_id, assigned_player_id),
	foreign key(war_id, player_id) references war_player(war_id, player_id) on delete cascade,
	foreign key(war_id, assigned_player_id) references war_player(war_id, player_id) on delete cascade
);

drop procedure if exists p_war_assignment_create;
delimiter //
create procedure p_war_assignment_create(varWarId int, varPlayerId int, varAssignedPlayerId int, varMessage varchar(1023), varDate datetime)
begin
    insert into war_assignment values(varWarId, varPlayerId, varAssignedPlayerId, varMessage, varDate);
    update war set date_modified = varDate where id = varWarId;
    select * from war_assignment where war_id = varWarId, player_id = varPlayerId, assigned_player_id = varAssignedPlayerId;
end //
delimiter ;

drop procedure if exists p_war_assignment_set;
delimiter //
create procedure p_war_assignment_set(varWarId int, varPlayerId int, varAssignedPlayerId int, varKey varchar(40), varValue text, varDate datetime)
begin
    set @st := concat('update war_assignment set ', varKey, ' = ', quote(varValue), ', date_modified = ', quote(varDate), ' where war_id = ', quote(varWarId), ' and player_id = ', quote(varPlayerId), ' and assigned_player_id = ', quote(varAssignedPlayerId));
    prepare stmt from @st;
    execute stmt;
end //
delimiter ;

drop procedure if exists p_war_assignment_delete;
delimiter //
create procedure p_war_assignment_delete(varWarId int, varPlayerId int, varAssignedPlayerId int)
begin
    delete from war_assignment where war_id = varWarId and player_id = varPlayerId and assigned_player_id = varAssignedPlayerId;
end //
delimiter ;

drop procedure if exists p_war_get_assignments;
delimiter //
create procedure p_war_get_assignments(varWarId int)
begin
    -- Only select assignments when the war is in preparation day
    select * from war_assignment where war_id = varWarId and status = 0;
end //
delimiter ;

alter table war add status int default 0;

drop procedure if exists p_player_update_bulk;
delimiter //
create procedure p_player_update_bulk(varId int, varRank varchar(2), varLevel int, varTrophies int, varDonations int, varReceived int, varLeagueUrl varchar(200), varDate datetime, varName varchar(50))
begin
    update player set name=varName, level=varLevel, trophies=varTrophies, donations=varDonations, received=varReceived, league_url=varLeagueUrl, date_modified=varDate where id = varId;
    update clan_member set rank=varRank where player_id = varId and rank != 5;
    if (varLevel <> (select stat_amount from player_stats where player_id = varId and stat_type = 'LV' order by date_recorded desc limit 1)
        or not exists (select * from player_stats where player_id = varId and stat_type = 'LV' limit 1))
        then insert into player_stats (player_id, date_recorded, stat_type, stat_amount) values (varId, varDate, 'LV', varLevel);
    end if;
    if (varTrophies <> (select stat_amount from player_stats where player_id = varId and stat_type = 'TR' order by date_recorded desc limit 1)
        or not exists (select * from player_stats where player_id = varId and stat_type = 'TR' limit 1))
        then insert into player_stats (player_id, date_recorded, stat_type, stat_amount) values (varId, varDate, 'TR', varTrophies);
    end if;
    if (varDonations <> (select stat_amount from player_stats where player_id = varId and stat_type = 'DO' order by date_recorded desc limit 1)
        or not exists (select * from player_stats where player_id = varId and stat_type = 'DO' limit 1))
        then insert into player_stats (player_id, date_recorded, stat_type, stat_amount) values (varId, varDate, 'DO', varDonations);
    end if;
    if (varReceived <> (select stat_amount from player_stats where player_id = varId and stat_type = 'RE' order by date_recorded desc limit 1)
        or not exists (select * from player_stats where player_id = varId and stat_type = 'RE' limit 1))
        then insert into player_stats (player_id, date_recorded, stat_type, stat_amount) values (varId, varDate, 'RE', varReceived);
    end if;
end //
delimiter ;