$(function(){
    $('#form-add').hide();
    $('#activate').click(function(){
        $('#form-add').slideToggle();
    });

    $('#addimg').click(function(){
        var selText = $('#body').selection();
        $('#body')
            .selection('insert', {text: '<img src="', mode: 'before'})
            .selection('insert', {text: '"/>', mode: 'after'});
    })
});