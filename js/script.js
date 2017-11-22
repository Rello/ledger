
var Ledger = function () {
    this.ajax_call_status = null;
    this.memberId = null;
    this.tmonth = null;
    this.group_id = null;
};

Ledger.prototype.getTotals = function(group_id){
    $.ajax({
        type : 'POST',
        url : OC.generateUrl('apps/ledger/gettotals'),
        data : {'group_id': group_id},
        success : function(jsondata) {
            if(jsondata.status === 'success' && jsondata.data !== 'nodata'){
                $('.kpi1').text(jsondata.data.kpi1);
                $('.kpi2').text(jsondata.data.kpi2);
                $('.kpi3').text(jsondata.data.kpi3);
            }
        }
    });
};

Ledger.prototype.getTimeline = function(group_id){
    $.ajax({
        type : 'POST',
        url : OC.generateUrl('apps/ledger/gettimeline'),
        data : {'group_id': group_id},
        success : function(jsondata) {
            if(jsondata.status === 'success' && jsondata.data !== 'nodata'){

                $('.timeline-row').remove('');

                $(jsondata.data.members).each(function(i,el){
                    var div = $('<div/>').attr({'class':'timeline-row'});
                    var divName = $('<div/>').attr({'data-member_id' : el.id,'data-user_id' : el.user_id,'data-name' : el.name,'class':'name'}).text(el.name).click(myLedger.editMember.bind(this));
                    var divAdd = $('<div/>').attr('class','month additem').text('+').click(myLedger.addTimeline.bind(this));
                    div.append(divName);
                    $(jsondata.data.timeline[el.id]).each(function(b,el2){
                        var divMonth = $('<div/>').attr({'data-memberid' : el.id, 'data-month' : el2.month, 'class':'month '+el2.version}).text(el2.month);
                        div.append(divMonth);
                    });
                    div.append(divAdd);
                    $('.timeline').append(div);
                });
                var div = $('<div/>').attr({'class':'timeline-row'});
                var divAdd = $('<div/>').attr('class','name additem').text('+ Kind').click(myLedger.addMember.bind(this));
                div.append(divAdd);
                $('.timeline').append(div);
            }
        }
    });
};

Ledger.prototype.getTransactions = function(group_id){
    $.ajax({
        type : 'POST',
        data : {'group_id': group_id},
        url : OC.generateUrl('apps/ledger/gettransactions'),
        success : function(jsondata) {
            if(jsondata.status === 'success' && jsondata.data !== 'nodata'){
                $('.transactions-row').remove('');
                $(jsondata.data).each(function(i,el){
                    var div = $('<div/>').attr('class','transactions-row');
                    var divDate = $('<div/>').attr('class','date').text(el.date);
                    var divType = $('<div/>').attr('class','type').text(el.type);
                    var divValue = $('<div/>').attr('class','value').text(el.value);
                    var divMember = $('<div/>').attr('class','member').text(el.member);
                    var divNote = $('<div/>').attr('class','note').text(el.note);
                    div.append(divDate);
                    div.append(divType);
                    div.append(divValue);
                    div.append(divMember);
                    div.append(divNote);
                    $('.transactions').append(div);
                });
            }
        }
    });
};
Ledger.prototype.addTimeline = function(evt){
    var $target = $(evt.target).prev();
    var memberId = $target.attr('data-memberid');
    var tmonth = $target.attr('data-month');
    $.ajax({
        type : 'POST',
        url : OC.generateUrl('apps/ledger/addtimeline'),
        data : {'member_id': memberId,
                'month': tmonth},
        success : function(ajax_data) {
            myLedger.getTimeline($this.group_id);
        }
    });
};

Ledger.prototype.addMember = function(evt){
    $.ajax({
        type : 'POST',
        url : OC.generateUrl('apps/ledger/addmember'),
        data : {'group_id': $this.group_id},
        success : function(ajax_data) {
            myLedger.getTimeline($this.group_id);
        }
    });
};

Ledger.prototype.editMember = function(evt){
    var $target = $(evt.target);
    var member_id = $target.attr('data-member_id');
    var user_id = $target.attr('data-user_id');
    var name = $target.attr('data-name');
    var group_id = $this.group_id;

    OC.dialogs.prompt(
        '',
        'Neuer Monatsbeitrag',
        function (result, password) {
            share.password = password;
            callback(result, share);
        },
        true,
        '',
        false
    ).then(myLedger._adjustDialogMember(member_id, user_id, name, group_id));
};

Ledger.prototype._adjustDialogMember = function(member_id, user_id, name, group_id, dummy) {
    var $dialog = $('.oc-dialog:visible');
    var $buttons = $dialog.find('button');
    // hack the buttons
    $dialog.find('.ui-icon').remove();
    var $dialogcontent = $dialog.find('.oc-dialog-content').html('');
    //var div = $('<div/>').attr('class','').text('Name: ' + name +', Member_id: ' + member_id);

    var label = $('<label/>').attr('for', 'member_name').text('Name:');
    var input = $('<input/>').attr({'type':'text', 'id': 'member_name'}).val(name);
    var divVersion = $('<div/>').attr('class','').text('Version: ');
    //$dialogcontent.append(div);
    $dialogcontent.append(label);
    $dialogcontent.append(input);
    $dialogcontent.append(divVersion);
    $buttons.eq(0).text(t('core', 'Löschen'));
    $buttons.eq(1).text(t('files_sharing', 'Aktualisieren'));
};



Ledger.prototype.editTimeline = function(evt){
    var $target = $(evt.target).prev();
    $this.memberId = $target.attr('data-memberid');
    $this.tmonth = $target.attr('data-month');

    OC.dialogs.prompt(
        '',
        'Neuer Monatsbeitrag',
        function (result, password) {
            share.password = password;
            callback(result, share);
        },
        true,
        '',
        false
    ).then(myLedger._adjustDialog);
};

Ledger.prototype._adjustDialog = function() {
    var $dialog = $('.oc-dialog:visible');
    var $buttons = $dialog.find('button');
    // hack the buttons
    $dialog.find('.ui-icon').remove();
    var $dialogcontent = $dialog.find('.oc-dialog-content').html('');
    var div = $('<div/>').attr('class','').text('Kind: ' + $this.memberId +', Monat: ' + $this.tmonth);
    var divVersion = $('<div/>').attr('class','').text('Version: ');
    $dialogcontent.append(div);
    $dialogcontent.append(divVersion);
    $buttons.eq(0).text(t('core', 'Löschen'));
    $buttons.eq(1).text(t('files_sharing', 'Aktualisieren'));
};

Ledger.prototype.addTimeline_backup = function(evt){
    var $target = $(evt.target).prev();
    var memberId = $target.attr('data-memberid');
    var month = $target.attr('data-month');
    $('#timeline-add-dialog').ocdialog({
        width : 500,
        modal: true,
        resizable: false,
        close : function() {
        }
    });
    $('#timeline-add-submit').click(function(){
        alert("test");
        $('#timeline-add-dialog').ocdialog('close');
    });


};

Ledger.prototype.reloadAll = function(){
    myLedger.getTotals($this.group_id);
    myLedger.getTimeline($this.group_id);
    myLedger.getTransactions($this.group_id);
};



$(document).ready(function() {
    myLedger = new Ledger();
    $this = this;

    $('#Piraten').click(function() {
        $this.group_id = 1;
        myLedger.reloadAll();
    });
    $('#Wusel').click(function() {
        $this.group_id = 2;
        myLedger.reloadAll();
    });
});