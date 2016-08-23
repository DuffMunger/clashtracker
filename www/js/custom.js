function showEditMessageForm(){
	$('#clanMessageDiv').hide();
	$('#editClanMessageDiv').show();
}
function showMessage(){
	$('#clanMessageDiv').show();
	$('#editClanMessageDiv').hide();
}
function saveMessage(id){
	var warId = '<?=$war->get('id');?>';
	var clanId = '<?=$clanId;?>';
	var message = $('#' + id).val();
	$.ajax({
		url: '/processUpdateWarMessage.php',
		method: 'POST',
		data: {
			warId: warId,
			clanId: clanId,
			message: message
		}
	}).done(function(xhr){
		data = jQuery.parseJSON(xhr);
		if(data.error){
			alert(data.error);
		}else{
			var message = data.message;
			$('#clanMessage').html(message);
			$('#newMessage').html(data.textarea);
			var height = (message.match(/\<br\>/g) || []).length+1;
			$('#newMessage').prop('rows', height)
			checkMessageStatus();
		}
	}).fail(function(xhr, textStatus){
		alert('There was an unexpected error. Please refresh the page and try again.');
	});
}
function checkMessageStatus(){
	if($('#clanMessage').html().length == 0){
		$('#clanMessageWrapper').addClass('hidden');
		$('#addMessageTab').removeClass('hidden');
		$('#tabs').removeClass('hidden');
		$('#addClanMessage').val('');
	}else{
		$('#warPlayersTab').click();
		$('#clanMessageWrapper').removeClass('hidden');
		$('#addMessageTab').addClass('hidden');
		if(!<?=json_encode($otherTabs);?>){
			$('#tabs').addClass('hidden');
		}
		showMessage();
	}
}
function changeOrder(playerId, clanId, action){
	var warId = '<?=$war->get('id');?>';
	$.ajax({
		url: '/processUpdateWarRank.php',
		method: 'POST',
		data: {
			warId: warId,
			playerId: playerId,
			clanId: clanId,
			action: action
		}
	}).done(function(xhr){
		data = jQuery.parseJSON(xhr);
		if(data.error){
			alert(data.error);
		}else{
			var player1Id = data.player1.id;
			var player2Id = data.player2.id;
			var row1 = $('.playerRow-' + player1Id);
			var row2 = $('.playerRow-' + player2Id);
			var temp = row1.html();
			row1.html(row2.html());
			row2.html(temp);
			row1Class = row1.attr('class');
			row2Class = row2.attr('class');
			row1.removeClass(row1Class).addClass(row2Class);
			row2.removeClass(row2Class).addClass(row1Class);
			var up = $('#up-' + player1Id);
			up.removeClass('hidden');
			if(data.player1.hideUp){
				up.addClass('hidden');
			}
			var down = $('#down-' + player1Id);
			down.removeClass('hidden');
			if(data.player1.hideDown){
				down.addClass('hidden');
			}
			var up = $('#up-' + player2Id);
			up.removeClass('hidden');
			if(data.player2.hideUp){
				up.addClass('hidden');
			}
			var down = $('#down-' + player2Id);
			down.removeClass('hidden');
			if(data.player2.hideDown){
				down.addClass('hidden');
			}
			$('.rank-' + player1Id).html(data.player1.rank);
			$('.rank-' + player2Id).html(data.player2.rank);
		}
	}).fail(function(xhr, textStatus){
		alert('There was an unexpected error. Please refresh the page and try again.');
	});
}
function clickRow(href){
	window.document.location = href;
}
var tabs = $('[role=presentation]').map(function(){return $(this).attr('name');});
for (var i = tabs.length - 1; i >= 0; i--) {
	$('#' + tabs[i] + 'Tab').on('click', function(){
		hideAllTabs();
		$(this).addClass('active');
		$('#' + $(this).attr('name')).removeClass('hidden');
	});
}
function hideAllTabs(){
	for (var i = tabs.length - 1; i >= 0; i--) {
		$('#' + tabs[i] + 'Tab').removeClass('active');
		$('#' + tabs[i]).addClass('hidden');
	}
}
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();   
});