alter table player_stats drop column deletable;
alter table player add column api_updated datetime default null;
alter table clan add column api_updated datetime default null;
drop procedure if exists p_player_record_loot;
delimiter //
create procedure p_player_record_loot(varId int, varGold int, varElixir int, varDarkElixir int, varDate varchar(50))
  begin
    update player set api_updated = varDate where id = varId;
    insert into player_stats (player_id, date_recorded, stat_type, stat_amount) values (varId, varDate, 'GO', varGold);
    insert into player_stats (player_id, date_recorded, stat_type, stat_amount) values (varId, varDate, 'EL', varElixir);
    insert into player_stats (player_id, date_recorded, stat_type, stat_amount) values (varId, varDate, 'DE', varDarkElixir);
  end //
delimiter ;

drop procedure if exists p_clan_players_for_loot_report;
delimiter //
create procedure p_clan_players_for_loot_report(varId int, varType varchar(2), varStartDate datetime, varEndDate datetime)
begin
  select player.* 
  from player_stats 
  join player 
    on player.id = player_id 
  where date_recorded > varStartDate 
    and date_recorded < varEndDate
    and stat_type = varType 
    and player_id in (
      select player_id 
      from clan_member 
      where clan_id = varId 
        and rank != 5) 
  group by player_id 
  having count(*) > 0;
end //
delimiter ;

drop procedure if exists p_clan_update_bulk;
delimiter //
create procedure p_clan_update_bulk(varId int, varName varchar(50), varType varchar(2), varDescription varchar(256), varFrequency varchar(2), varMinTrophies int, varMembers int, varClanPoints int, varClanLevel int, varWarWins int, varBadgeUrl varchar(200), varLocation varchar(50), varApiUpdated datetime, varHourAgo datetime)
    begin
        update clan set name=varName, clan_type=varType, description=varDescription, war_frequency=varFrequency, minimum_trophies=varMinTrophies, members=varMembers, clan_points=varClanPoints, clan_level=varClanLevel, war_wins=varWarWins, badge_url=varBadgeUrl, location=varLocation, api_updated=varApiUpdated where id = varId;
        if (varClanPoints <> (select stat_amount from clan_stats where clan_id = varId and stat_type = 'CP' order by date_recorded desc limit 1)
            or not exists (select * from clan_stats where clan_id = varId and stat_type = 'CP' limit 1))
        then insert into clan_stats (clan_id, date_recorded, stat_type, stat_amount) values (varId, varApiUpdated, 'CP', varClanPoints);
        end if;
        if (varClanLevel <> (select stat_amount from clan_stats where clan_id = varId and stat_type = 'CL' order by date_recorded desc limit 1)
            or not exists (select * from clan_stats where clan_id = varId and stat_type = 'CL' limit 1))
        then insert into clan_stats (clan_id, date_recorded, stat_type, stat_amount) values (varId, varApiUpdated, 'CL', varClanLevel);
        end if;
        if (varMembers <> (select stat_amount from clan_stats where clan_id = varId and stat_type = 'ME' order by date_recorded desc limit 1)
            or not exists (select * from clan_stats where clan_id = varId and stat_type = 'ME' limit 1))
        then insert into clan_stats (clan_id, date_recorded, stat_type, stat_amount) values (varId, varApiUpdated, 'ME', varMembers);
        end if;
        if (varWarWins <> (select stat_amount from clan_stats where clan_id = varId and stat_type = 'WW' order by date_recorded desc limit 1)
            or not exists (select * from clan_stats where clan_id = varId and stat_type = 'WW' limit 1))
        then insert into clan_stats (clan_id, date_recorded, stat_type, stat_amount) values (varId, varApiUpdated, 'WW', varWarWins);
        end if;
    end //
delimiter ;