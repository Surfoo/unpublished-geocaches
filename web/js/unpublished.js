$().ready(function() {

    fetchUnpublishedCachesFromGM();

    if (logged == 'true') {
        $('#refresh-cache').trigger('click');
    }

});

function fetchUnpublishedCaches() {
    $('#refresh-cache').button('loading');
    $('#fetching-unpublished-caches').show();
    $.ajax({
        url: "unpublished.php",
        async: false,
        success: function(data) {
            $('#fetching-unpublished-caches').hide();
            $('#select-all').prop('checked', false);
            $('#refresh-cache').button('reset');
            if (!data || data === "" || typeof data != 'object') {
                return false;
            }
            if (data && !data.success) {
                alert(data.message);
            } else {
                $('#table-unpublished-caches').show();
                $('#table-caches tbody').html('');

                $.each(data.unpublishedCaches, function(guid, title) {
                    $('#table-caches tbody')
                        .append('<tr class="' + guid + '">\n' +
                            '   <td><input type="checkbox" name="cache" class="unpublished-geocache" value="' + guid + '" id="' + guid + '" /></td>\n' +
                            '   <td><label for="' + guid + '">' + title + '</label></td>\n' +
                            '   <td class="status"> </td>\n' +
                            '</tr>\n');
                });
            }
        },
        failure: function() {}
    });
}

function fetchUnpublishedCachesFromGM() {

    //$('#unpublishedCachesBlock-gm').show();

    $.ajax({
        url: "unpublished-gm.php",
        success: function(data) {
            if (!data || data === "" || typeof data != 'object') {
                return false;
            }
            if (data && !data.success) {
                alert(data.message);
                return false;
            }
            if (data.unpublishedCaches) {
                $('#table-unpublished-caches-gm').show();
                $('#table-caches-gm tbody').html('');

                $.each(data.unpublishedCaches, function(guid, title) {
                    $('#table-caches-gm tbody')
                        .append('<tr>\n' +
                            '   <td><input type="checkbox" name="cache-gm" class="unpublished-geocache-gm" value="' + guid + '" id="' + guid + '-gm" /></td>\n' +
                            '   <td><label for="' + guid + '-gm">' + title + '</label></td>\n' +
                            '</tr>\n');
                });
            }
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
            },
            failure: function() {}
        });
    } else if ($(this).text() == "Sign out") {

        $('#unpublishedCachesBlock').hide();
        btn.button('loading');

        $.ajax({
            url: "login.php",
            type: "POST",
            async: true,
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
});

$('#select-all-gm').click(function() {
    $('.unpublished-geocache-gm').prop('checked', $(this).is(":checked"));
});

$('#refresh-cache').click(function() {
    fetchUnpublishedCaches();
});

$('#refresh-cache-gm').click(function() {
    $(this).button('loading');
    fetchUnpublishedCachesFromGM();
    $('#select-all-gm').prop('checked', false);
    $(this).button('reset');
});

$('#create-gpx').click(function() {
    var list = [];

    $('input[name=cache]:checked').each(function() {
        list.push(this.value);
    });

    if (list.length <= 0) {
        alert('You must choose at least one cache.');
        return false;
    }

    $('#download-gpx').remove();
    $('#download-wpts').remove();
    $('#table-caches tbody tr').removeClass('success');
    $('#table-caches tbody tr').removeClass('danger');
    $('#table-caches .status').html('');
    $(this).button('loading');

    var gpx = [];
    $.each(list, function(index, guid) {
        $.ajax({
            url: "geocaches.php",
            type: "POST",
            async: false,
            data: {
                'guid': guid
            },
            beforeSend: function() {
                $('.' + guid + ' .status').html('<img src="loader.gif" alt="">');
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
        animation: false
    });

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
                    if (data.link_wpts) {
                        $('#table-unpublished-caches').append('&nbsp;' + data.link_wpts);
                    }
                }
            },
            failure: function() {}
        });
    }

    $(this).button('reset');
});

$('#create-gpx-gm').click(function() {
    var list = [];

    $('input[name=cache-gm]:checked').each(function() {
        list.push(this.value);
    });

    if (list.length <= 0) {
        alert('You must choose at least one cache.');
        return false;
    }

    $('#download-gpx-gm').remove();
    $('#download-wpts-gm').remove();
    $(this).button('loading');

    if (list.length > 0) {
        $.ajax({
            url: "download.php",
            type: "POST",
            data: {
                'guid': list,
                'greasemonkey': true
            },
            success: function(data) {
                if (data && data.success) {
                    $('#table-unpublished-caches-gm').append(data.link);
                    if (data.link_wpts) {
                        $('#table-unpublished-caches-gm').append('&nbsp;' + data.link_wpts);
                    }
                }
            },
            failure: function() {}
        });
    }
    $(this).button('reset');

});

var cookieRegistry = [];

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function listenCookieChange(cookieName, callback) {
    setInterval(function() {
        if (cookieRegistry[cookieName]) {
            if (readCookie(cookieName) != cookieRegistry[cookieName]) {
                // update registry so we dont get triggered again
                cookieRegistry[cookieName] = readCookie(cookieName);
                return callback();
            }
        } else {
            cookieRegistry[cookieName] = readCookie(cookieName);
        }
    }, 1000);
}

// bind the listener
listenCookieChange('unpublished', function() {
    $('#refresh-cache-gm').trigger('click');
});