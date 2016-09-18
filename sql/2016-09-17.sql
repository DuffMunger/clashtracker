drop procedure if exists p_clan_get_current_members;
delimiter //
create procedure p_clan_get_current_members(varClanId int, varOrder varchar(50))
begin
	set @st := concat('select player.*, rank, war_rank from clan_member join player on player_id = player.id where clan_id = ', varClanId, ' and rank != 5 order by ', varOrder);
	prepare stmt from @st;
	execute stmt;
end //
delimiter ;

drop procedure if exists p_clan_update_player_war_rank;
delimiter //
create procedure p_clan_update_player_war_rank(varClanId int, varPlayerId int, varWarRank int, varDateModified datetime)
begin
	update clan_member set war_rank = war_rank + 1, date_modified = varDateModified where clan_id = varClanId and war_rank >= varWarRank;
	update clan_member set war_rank = varWarRank, date_modified = varDateModified where clan_id = varClanId and player_id = varPlayerId;
end //
delimiter ;