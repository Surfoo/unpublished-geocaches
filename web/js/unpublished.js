(function() {
    'use strict';

    var fetchUnpublishedCaches = function() {
        $('#refresh-cache').button('loading');
        $.ajax({
            url: 'unpublished.php',
            success: function(data) {
                $('#select-all').prop('checked', false);
                $('#fetching-unpublished-caches').hide();
                $('#refresh-cache').button('reset');
                if (!data || data === '' || typeof data !== 'object') {
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
                    $('#table-caches tbody').show();
                }
            }
        });
    },

        fetchUnpublishedCachesFromGM = function() {
            //$('#unpublishedCachesBlock-gm').show();
            $.ajax({
                url: 'unpublished-gm.php',
                success: function(data) {
                    if (!data || data === '' || typeof data !== 'object') {
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
                }
            });
        };

    $('#login').click(function() {
        var btn = $(this);
        if ($(this).text() === 'Sign in') {

            if ($('#username').val() === '' || $('#password').val() === '') {
                btn.button('reset');
                return false;
            }

            btn.button('loading');

            $.ajax({
                url: 'login.php',
                type: 'POST',
                data: {
                    username: $('#username').val(),
                    password: $('#password').val()
                },
                success: function(data) {
                    if (!data || data === '' || typeof data !== 'object') {
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
                    $('#fetching-unpublished-caches').show();
                    fetchUnpublishedCaches();
                }
            });
        } else if ($(this).text() === 'Sign out') {

            $('#unpublishedCachesBlock').hide();
            btn.button('loading');

            $.ajax({
                url: 'login.php',
                type: 'POST',
                data: {
                    signout: true
                },
                success: function(data) {
                    if (!data || data === '' || typeof data !== 'object') {
                        return false;
                    }
                    if (data && data.success) {
                        location.reload();
                    }
                }
            });
        }
    });

    $('#select-all').click(function() {
        $('.unpublished-geocache').prop('checked', $(this).is(':checked'));
    });

    $('#select-all-gm').click(function() {
        $('.unpublished-geocache-gm').prop('checked', $(this).is(':checked'));
    });

    $('#refresh-cache').click(function() {
        $('#table-caches tbody').slideUp('400', fetchUnpublishedCaches());
    });

    $('#refresh-cache-gm').click(function() {
        $(this).button('loading');
        fetchUnpublishedCachesFromGM();
        $('#select-all-gm').prop('checked', false);
        $(this).button('reset');
    });

    $('#create-gpx').click(function() {
        var list = [],
            create = $(this);

        $('input[name=cache]:checked').each(function() {
            list.push(this.value);
        });

        if (list.length <= 0) {
            alert('You must choose at least one cache.');
            return false;
        }

        $('#download-links').html('');
        $('#table-caches tbody tr').removeClass('success');
        $('#table-caches tbody tr').removeClass('danger');
        $('#table-caches .status').html('');
        create.button('loading');

        var getGeocache = function(guid) {
            return $.ajax({
                url: 'geocaches.php',
                type: 'POST',
                data: {
                    'guid': guid
                },
                beforeSend: function() {
                    $('.' + guid + ' .status').html('<img src="loader.gif" alt="">');
                },
                success: function(data) {
                    if (data && data.success) {
                        $('.' + data.guid).addClass('success');
                        $('.' + data.guid + ' .status').html('<span class="glyphicon glyphicon-ok"></span>');
                    } else {
                        $('.' + data.guid).addClass('danger');
                        $('.' + data.guid + ' .status').html('<span class="glyphicon glyphicon-remove" data-content="' + data.message + '"></span>');
                    }
                }
            });
        };

        //from http://blog.xebia.fr/2012/11/28/les-objets-differes-et-les-promesses-en-jquery/
        //On transforme la liste d'identifiants en liste de promesses de noms correspondant à ces id

        var listOfPromises = list.map(getGeocache);

        // La fonction when ne prend pas de tableau en entrée, mais un varargs.
        // Il est donc nécessaire de passer par la fonction apply pour invoquer when,
        // afin de transformer le tableau de promesses en varargs
        $.when.apply($, listOfPromises).then(function() {
            var gpx = [];
            if (listOfPromises.length > 1) {
                for (var i = 0, length = listOfPromises.length; i < length; ++i) {
                    //Arguments est une variable magique contenant les paramètres de la fonction
                    //Les paramètres sont passés dans le même ordre que les promesses.
                    if (arguments[i][0].success) {
                        gpx.push(arguments[i][0].guid);
                    }
                }
            } else if (arguments[0].success) {
                gpx.push(arguments[0].guid);
            }

            $('tbody span').popover({
                trigger: 'hover',
                animation: false
            });

            if (gpx.length > 0) {
                $.ajax({
                    url: 'download.php',
                    type: 'POST',
                    data: {
                        'guid': gpx
                    },
                    success: function(data) {
                        if (data && data.success) {
                            $('#download-links').append(data.link);
                            if (data.link_wpts) {
                                $('#download-links').append('&nbsp;' + data.link_wpts);
                            }
                        }
                    }
                });
            }
            create.button('reset');
        });
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

        $('#download-links-gm').html('');
        $(this).button('loading');

        if (list.length > 0) {
            $.ajax({
                url: 'download.php',
                type: 'POST',
                data: {
                    'guid': list,
                    'greasemonkey': true
                },
                success: function(data) {
                    if (data && data.success) {
                        $('#download-links-gm').append(data.link);
                        if (data.link_wpts) {
                            $('#download-links-gm').append('&nbsp;' + data.link_wpts);
                        }
                    }
                }
            });
        }
        $(this).button('reset');
    });

    var cookieRegistry = [],
        readCookie = function(name) {
            var nameEQ = name + '=';
            var ca = document.cookie.split(';');
            for (var i = 0, length = ca.length; i < length; ++i) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        },

        listenCookieChange = function(cookieName, callback) {
            setInterval(function() {
                if (cookieRegistry[cookieName]) {
                    if (readCookie(cookieName) !== cookieRegistry[cookieName]) {
                        // update registry so we dont get triggered again
                        cookieRegistry[cookieName] = readCookie(cookieName);
                        return callback();
                    }
                } else {
                    cookieRegistry[cookieName] = readCookie(cookieName);
                }
            }, 1000);
        };

    // bind the listener
    listenCookieChange('unpublished', function() {
        $('#refresh-cache-gm').trigger('click');
    });

    $().ready(function() {
        fetchUnpublishedCachesFromGM();
        if (logged === 'true') {
            $('#fetching-unpublished-caches').show(0, fetchUnpublishedCaches);
        }
    });
}());