drop procedure if exists p_war_delete;
delimiter //
create procedure p_war_delete(varId int)
begin
	delete from war_edit_requests where war_id = varId;
	delete from war_allowed_users where war_id = varId;
	delete from war_attack where war_id = varId;
	delete from war_player where war_id = varId;
	delete from war where id = varId;
end //
delimiter ;