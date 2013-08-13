$().ready(function() {
    if (logged == 'true') {
        $('#unpublishedCachesBlock').show();
        $('#refresh-cache').trigger('click');
        $('#fetching-unpublished-caches').hide();
    }
});

function fetchUnpublishedCaches() {
    $.ajax({
        url: "unpublished.php",
        async: false,
        success: function(data) {
            if (!data || data === "" || typeof data != 'object') {
                return false;
            }
            if (data && !data.success) {
                alert(data.message);
                return false;
            }

            $('#table-unpublished-caches').show();
            $('#table-caches tbody').html('');

            $.each(data.unpublishedCaches, function(key, value) {
                $('#table-caches tbody')
                    .append('<tr class="' + key + '">\n' +
                        '   <td><input type="checkbox" name="cache" class="unpublished-geocache" value="' + key + '" id="' + key + '" /></td>\n' +
                        '   <td><label for="' + key + '">' + value + '</label></td>\n' +
                        '   <td class="status"> </td>\n' +
                        '</tr>\n');
            });
        },
        failure: function() {}
    });
}

$('#login').click(function() {
    var btn = $(this);
    if ($(this).text() == "Sign in") {

        if ($('#username').val() == '' || $('#password').val() == '') {
            btn.button('reset');
            return false;
        }

        btn.button('loading');

        $.ajax({
            url: "login.php",
            type: "POST",
            async: false,
            data: {
                username: $('#username').val(),
                password: $('#password').val(),

            },
            success: function(data) {
                if (!data || data === "" || typeof data != 'object') {
                    return false;
                }
                if (data && !data.success) {
                    btn.button('reset');
                    alert(data.message);
                    return false;
                }
                $('#username').remove();
                $('#password').remove();
                $('#gc-form-login').prepend('<span id="signin">Hello ' + data.username + '!</span>');
                btn.button('signout');

                $('#unpublishedCachesBlock').show();
                fetchUnpublishedCaches();
                $('#fetching-unpublished-caches').hide();
            },
            failure: function() {}
        });
    } else if ($(this).text() == "Sign out") {

        $('#unpublishedCachesBlock').hide();
        btn.button('loading');

        $.ajax({
            url: "login.php",
            type: "POST",
            async: false,
            data: {
                signout: true,
            },
            success: function(data) {
                if (!data || data === "" || typeof data != 'object') {
                    return false;
                }
                if (data && data.success) {
                    location.reload();
                }
            },
            failure: function() {}
        });
    }
});

$('#select-all').click(function() {
    $('.unpublished-geocache').prop('checked', $(this).is(":checked"));
})

$('#refresh-cache').click(function() {
    $(this).button('loading');
    fetchUnpublishedCaches();
    $('#select-all').prop('checked', false);
    $(this).button('reset');
})


$('#create-gpx').click(function() {
    var list = [];

    $('input[name=cache]:checked').each(function() {
        list.push(this.value);
    });

    if (list.length <= 0) {
        alert('You must choose at least one cache.');
        return false;
    }

    $('#downloadLink').html('');
    $('#table-caches tbody tr').removeClass('success');
    $('#table-caches tbody tr').removeClass('danger');
    $('#table-caches .status').html('');
    $('#download-gpx').remove();
    $(this).button('loading');

    var gpx = [];
    $.each(list, function(index, guid) {
        $('.' + guid + ' .status').html('<img src="loader.gif" alt="">');
        $.ajax({
            url: "geocaches.php",
            type: "POST",
            async: false,
            data: {
                'guid': guid
            },
            success: function(data) {
                if (data && data.success) {
                    gpx.push(data.guid);
                    $('.' + data.guid).addClass('success');
                    $('.' + data.guid + ' .status').html('<span class="glyphicon glyphicon-ok"></span>');
                } else {
                    $('.' + data.guid).addClass('danger');
                    $('.' + data.guid + ' .status').html('<span class="glyphicon glyphicon-remove" data-content="' + data.message + '"></span>');
                }
            },
            failure: function() {}
        });
    });

    $('tbody span').popover({
        trigger: 'hover',
        animation: false,
        html: true
    });

    $(this).button('reset');

    if (gpx.length > 0) {
        $.ajax({
            url: "download.php",
            type: "POST",
            data: {
                'guid': gpx
            },
            success: function(data) {
                if (data && data.success) {
                    $('#table-unpublished-caches').append(data.link);
                }
            },
            failure: function() {}
        });

    }

    return false;
});
