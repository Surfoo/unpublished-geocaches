<!DOCTYPE html>
<html lang="en">
    <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
        <title>Unpublished Geocaches</title>
        <meta name="description" content="Fetch your unpublished Geocaches and create a GPX with.">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0-rc1/css/bootstrap.min.css">
        <link rel="stylesheet" href="css/bootstrap-glyphicons.css">
        <link rel="stylesheet" href="css/design.css">
        <script type="text/javascript">
        var logged = {% if logged == 'false' %}'false'{% else %}'true'{% endif %};
        </script>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <header>
                    <h1>Unpublished Geocaches</h1>
                    <p class="alert">If you don't trust this application, please don't use it, <a href="https://github.com/Surfoo/unpublished-geocaches">download the code on github</a> and use it on your own server.</p>
                </header>
                <form id="gc-form-login" class="form-inline">
                    {% if logged == 'false' %}
                        <input type="text" id="username" class="form-control input-small" placeholder="Geocaching Username" required>
                        <input type="password" id="password" class="form-control input-small" placeholder="Geocaching Password" autocomplete="on" required>
                        <button type="button" data-loading-text="Loading..." data-signout-text="Sign out" class="btn btn-default btn-small" id="login">Sign in</button>
                    {% else %}
                        <span id="signin" >Hello {{ username }}!</span>
                        <button type="button" data-loading-text="Loading..." class="btn btn-default btn-small" id="login">Sign out</button>
                    {% endif %}
                </form>
                <hr />

                <div id="unpublishedCachesBlock">
                    <div id="fetching-unpublished-caches">Fetching your unpublished caches...</div>
                    <div id="table-unpublished-caches">
                        <h3>Your unpublished geocaches</h3>
                        <table id="table-caches" class="table table-hover table-condensed">
                            <thead>
                                <tr>
                                    <th id="head-pick"><input type="checkbox" id="select-all" title="Select All" /></th>
                                    <th id="head-title">Title</th>
                                    <th id="head-status">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <button type="submit" class="btn btn-primary" data-loading-text="Refreshing..." id="refresh-cache">Refresh the list</button>
                        <button type="submit" class="btn btn-primary" data-loading-text="Creating..." id="create-gpx">Create a GPX</button>
                    </div>
                </div>

                <div id="downloadLink"></div>
            </div>
        </div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0-rc1/js/bootstrap.min.js"></script>
        <script src="js/unpublished.js"></script>
    </body>
</html>
