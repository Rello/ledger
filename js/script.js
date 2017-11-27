
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
            $('.timeline-row').remove('');
            if(jsondata.status === 'success' && jsondata.data !== 'nodata'){
                $(jsondata.data.members).each(function(i,el){
                    var div = $('<div/>').attr({'class':'timeline-row'});
                    var divName = $('<div/>').attr({'data-member_id' : el.id,'data-user_id' : el.user_id,'data-name' : el.name,'class':'name'}).text(el.name).click(myLedger.editMember.bind(this));
                    var divAdd = $('<div/>').attr('class','month additem').text('+').click(myLedger.addTimeline.bind(this));
                    div.append(divName);

                    $(jsondata.data.timeline[el.id]).each(function(b,el2){
                        var divMonth = $('<div/>').attr({'data-id' : el2.id, 'data-member_id' : el.id, 'data-month' : el2.month, 'data-version' : el2.version, 'class':'month '+el2.version}).text(el2.month).click(myLedger.editTimeline.bind(this));
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

Ledger.prototype.addTimeline = function(evt){
    var $target = $(evt.target).prev();
    var memberId = $target.attr('data-member_id');
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

Ledger.prototype.editTimeline = function(evt){
    $('#edit_dialog').remove();
    var $target = $(evt.target);
    $target.off();
    var div = $('<div/>').attr('id','edit_dialog');
    var input_month = $('<input/>').attr({'type':'text', 'id': 'edit_month', 'style': 'width: 88px;'}).val($target.attr('data-month'));
    var input_version = $('<input/>').attr({'type':'text', 'id': 'edit_version', 'style': 'width: 88px;'}).val($target.attr('data-version'));
    var input_id = $('<input/>').attr({'type':'text', 'id': 'edit_id', 'style': 'display: none;'}).val($target.attr('data-id'));
    var input_button_ok = $('<input/>').attr({'type':'button', 'class': 'icon-checkmark', 'title': 'Speichern'}).click(myLedger.saveTimeline.bind(this));
    var input_button_close = $('<input/>').attr({'type':'button', 'class': 'icon-close', 'title': 'Abbrechen'}).click(myLedger.cancelTimeline.bind(this));
    var input_button_delete = $('<input/>').attr({'type':'button', 'class': 'icon-delete', 'title': 'Löschen'}).click(myLedger.deleteTimeline.bind(this));
    div.append(input_month);
    div.append(input_version);
    div.append(input_id);
    div.append(input_button_close);
    div.append(input_button_delete);
    div.append(input_button_ok);
    $target.html(div);
};

Ledger.prototype.saveTimeline = function(evt){
    var id = $('#edit_id').val();
    var month = $('#edit_month').val();
    var version = $('#edit_version').val();

    $.ajax({
        type : 'POST',
        url : OC.generateUrl('apps/ledger/edittimeline'),
        data : {'id': id,
            'month': month,
            'version': version
        },
        success : function(ajax_data) {
            myLedger.getTimeline($this.group_id);
        }
    });
    $('#edit_dialog').text(month);
};

Ledger.prototype.cancelTimeline = function(){
    $('#edit_dialog').text($('#edit_month').val());
    myLedger.getTimeline($this.group_id);
};

Ledger.prototype.deleteTimeline = function(){
    var id = $('#edit_id').val();
    $.ajax({
        type : 'POST',
        url : OC.generateUrl('apps/ledger/deletetimeline'),
        data : {'id': id
        },
        success : function(ajax_data) {
            myLedger.reloadAll();
        }
    });
    $('#edit_dialog').remove();
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
    $('#edit_dialog').remove();
    var $target = $(evt.target);
    $target.off();
    var div = $('<div/>').attr('id','edit_dialog');
    var input_name = $('<input/>').attr({'type':'text', 'id': 'edit_member_name', 'size': '10'}).val($target.attr('data-name'));
    var label_user_id = $('<label/>').attr({'for': 'edit_user_id'}).text('NC-User');
    var input_user_id = $('<input/>').attr({'type':'text', 'id': 'edit_user_id', 'size': '10'}).val($target.attr('data-user_id'));
    var input_member_id = $('<input/>').attr({'type':'text', 'id': 'edit_member_id', 'style': 'display: none;'}).val($target.attr('data-member_id'));
    var input_button_ok = $('<input/>').attr({'type':'button', 'class': 'icon-checkmark', 'title': 'Speichern'}).click(myLedger.saveMember.bind(this));
    var input_button_close = $('<input/>').attr({'type':'button', 'class': 'icon-close', 'title': 'Abbrechen'}).click(myLedger.cancelMember.bind(this));
    var input_button_delete = $('<input/>').attr({'type':'button', 'class': 'icon-delete', 'title': 'Löschen'}).click(myLedger.deleteMember.bind(this));
    div.append(input_name);
    div.append(label_user_id);
    div.append(input_user_id);
    div.append(input_member_id);
    div.append(input_button_close);
    div.append(input_button_delete);
    div.append(input_button_ok);
    $target.html(div);
};

Ledger.prototype.saveMember = function(evt){
    var member_id = $('#edit_member_id').val();
    var user_id = $('#edit_user_id').val();
    var name = $('#edit_member_name').val();

    $.ajax({
        type : 'POST',
        url : OC.generateUrl('apps/ledger/editmember'),
        data : {'member_id': member_id,
            'user_id': user_id,
            'name': name
        },
        success : function(ajax_data) {
            myLedger.getTimeline($this.group_id);
        }
    });
    $('#edit_dialog').text(name);
};

Ledger.prototype.cancelMember = function(){
    $('#edit_dialog').text($('#edit_member_name').val());
    myLedger.getTimeline($this.group_id);
};

Ledger.prototype.deleteMember = function(){
    var member_id = $('#edit_member_id').val();

    $.ajax({
        type : 'POST',
        url : OC.generateUrl('apps/ledger/deletemember'),
        data : {'member_id': member_id
        },
        success : function(ajax_data) {
            myLedger.getTimeline($this.group_id);
        }
    });
    $('#edit_dialog').remove();
};

Ledger.prototype.getTransactions = function(group_id){
    $.ajax({
        type : 'POST',
        data : {'group_id': group_id},
        url : OC.generateUrl('apps/ledger/gettransactions'),
        success : function(jsondata) {
            $('.transactions-row').remove('');
            if(jsondata.status === 'success' && jsondata.data !== 'nodata'){
                $(jsondata.data).each(function(i,el){
                    var div = $('<div/>').attr('class','transactions-row');
                    var divDate = $('<div/>').attr('class','date').text(el.date);
                    var divType = $('<div/>').attr('class','type').text(el.type);
                    var divValue = $('<div/>').attr('class','value').text(el.value);
                    var divMember = $('<div/>').attr('class','member').text(el.member);
                    var divMonth = $('<div/>').attr('class','month').text(el.month);
                    var divNote = $('<div/>').attr('class','note').text(el.note);
                    div.append(divDate);
                    div.append(divType);
                    div.append(divValue);
                    div.append(divMember);
                    div.append(divMonth);
                    div.append(divNote);
                    $('.transactions').append(div);
                });
            }
        }
    });
};

Ledger.prototype.saveTransaction = function(){
    var type = $('#transaction_input_type').val();
    var value = $('#transaction_input_value').val();
    var member = $('#transaction_input_member').val();
    var note = $('#transaction_input_note').val();
    var date = $('#transaction_input_date').val();

    $.ajax({
        type : 'POST',
        url : OC.generateUrl('apps/ledger/addtransaction'),
        data : {'valuetype': type,
            'value': value,
            'member_id': member,
            'note': note,
            'date': date
        },
        success : function(ajax_data) {
            myLedger.getTransactions($this.group_id);
            $('#transaction_input_type').val('');
            $('#transaction_input_value').val('');
            $('#transaction_input_member').val('');
            $('#transaction_input_note').val('');
            $('#transaction_input_date').val('');

        }
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

    $('#transaction_save').click(function() {
        myLedger.saveTransaction();
    });
});