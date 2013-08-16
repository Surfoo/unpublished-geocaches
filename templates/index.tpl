<!DOCTYPE html>
<html lang="en">
    <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
        <title>Unpublished Geocaches</title>
        <meta name="description" content="Fetch your unpublished Geocaches and create a GPX with.">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0-rc2/css/bootstrap.min.css">
        <link rel="stylesheet" href="css/bootstrap-glyphicons.css">
        <link rel="stylesheet" href="css/design.css">
        <script type="text/javascript">
        var logged = {% if logged == 'false' %}'false'{% else %}'true'{% endif %};
        </script>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <header>
                        <h1>Unpublished Geocaches</h1>
                        <p class="alert">If you don't trust this application, please don't use it, <a href="https://github.com/Surfoo/unpublished-geocaches">download the code on github</a> and use it on your own server.</p>

                    <form id="gc-form-login" class="form-inline">
                        {% if logged == 'false' %}
                            <input type="text" id="username" class="form-control input-sm" placeholder="Geocaching Username" required>
                            <input type="password" id="password" class="form-control input-sm" placeholder="Geocaching Password" autocomplete="on" required>
                            <button type="button" data-loading-text="Loading..." data-signout-text="Sign out" class="btn btn-primary btn-sm" id="login">Sign in</button>
                        {% else %}
                            <span id="signin" >Hello {{ username }}!</span>
                            <button type="button" data-loading-text="Loading..." class="btn btn-primary btn-sm" id="login">Sign out</button>
                        {% endif %}
                    </form>
                    <div class="pull-right">
                        <span id="" href="#help" data-toggle="modal" title="Need help?" class="glyphicon glyphicon-question-sign"></span>
                    </div>
                    </header>
                    <hr />

                    <h3>Your unpublished geocaches</h3>
                </div>
                <div class="col-md-6">
                    <div id="unpublishedCachesBlock">
                        <h4>From your account</h4>
                        <div id="fetching-unpublished-caches" class="well well-sm"><img src="loader.gif" alt="" /> Fetching your unpublished caches...</div>
                        <div id="table-unpublished-caches">
                            <table id="table-caches" class="table table-hover table-condensed">
                                <thead>
                                    <tr>
                                        <th class="head-pick"><input type="checkbox" id="select-all" title="Select All" /></th>
                                        <th class="head-title">Title</th>
                                        <th class="head-status">Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <button type="submit" class="btn btn-primary" data-loading-text="Refreshing..." id="refresh-cache">Refresh the list</button>
                            <button type="submit" class="btn btn-primary" data-loading-text="Creating..." id="create-gpx">Create a GPX</button>
                        </div>
                    </div>

                </div>

                <div class="col-md-6">

                    <div id="unpublishedCachesBlock-gm">
                        <h4>From greasemonkey</h4>
                        <div id="table-unpublished-caches-gm">
                            <table id="table-caches-gm" class="table table-hover table-condensed">
                                <thead>
                                    <tr>
                                        <th class="head-pick"><input type="checkbox" id="select-all-gm" title="Select All" /></th>
                                        <th class="head-title">Title</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <button type="submit" class="btn btn-primary" data-loading-text="Refreshing..." id="refresh-cache-gm">Refresh the list</button>
                            <button type="submit" class="btn btn-primary" data-loading-text="Creating..." id="create-gpx-gm">Create a GPX</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div id="help" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Modal title</h4>
                    </div>
                    <div class="modal-body">
                        <p>One fine body&hellip;</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0-rc2/js/bootstrap.min.js"></script>
        <script src="js/unpublished.js"></script>
    </body>
</html>
