<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Unpublished Geocaches</title>
        <meta name="description" content="Fetch your unpublished Geocaches and create a GPX with them.">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="css/design.css?{{ constant('SUFFIX_CSS_JS') }}">
        <script type="text/javascript">
        var logged = {% if logged == 'false' %}'false'{% else %}'true'{% endif %};
        </script>
    </head>
    <body>
        <div id="help" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Need help?</h4>
                    </div>
                    <div class="modal-body">
                        <p>The "Unpublished Geocaches" app allows you to create a GPX file from your unpublished geocaches, to prepare an event for example.<br />
                        There are 3 ways to use this web app : </p>
                        <h5><strong>1st method:</strong></h5>
                        <div class="method">
                            <p>You can retrieve all your unpublished geocaches by logging with your username and password.</p>
                            <p class="alert alert-warning">Your username and password info is not saved by this app.<br />
                            If you don't trust this application, <strong>please don't use it</strong>. Instead use the 2nd or 3rd method.
                        </div>

                        <h5><strong>2nd method:</strong></h5>
                        <div class="method">
                            <p>This method is slower than the first, but doesn't require your password.<p>
                            <ul>
                                <li><strong>For Firefox users:</strong> First, you must install <a href="https://addons.mozilla.org/fr/firefox/addon/greasemonkey/" onclick="window.open(this.href);return false;">the greasemonkey addon</a>.</li>
                                <li><strong>For Chrome users:</strong> First, you must install <a href="https://chrome.google.com/webstore/detail/tampermonkey/dhdgffkkebhmkfjojejmpbldmpobfkfo?hl=fr" onclick="window.open(this.href);return false;">the tampermonkey addon</a>.</li>
                            </ul>
                            <p>Then install the <a href="/unpublished.user.js" onclick="window.open(this.href);return false;">Unpublished Geocaches script</a>. After installation, a "Send to Unpublished Geocaches" button appears on your caches listing, just click it, and the content of the webpage will be sent to this web app.<br />
                            Refresh the page, and titles of your sent caches appear.</p>
                        </div>

                        <h5><strong>3rd method:</strong></h5>
                        <div class="method">The "Unpublished Geocaches" app is free and open source, you can <a href="https://github.com/Surfoo/unpublished-geocaches">download the code from GitHub</a> and use it on your own server.
                        </div>

                        <h5><strong>Any questions?</strong></h5>
                        <div class="method">You can also contact me: surfooo at gmail dot com
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <header>
                        <h1>Unpublished Geocaches</h1>
                        <div class="alert alert-warning alert-dismissable fade in">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            Please, read <a href="#help" data-toggle="modal">the help section</a> before you use this web app.
                        </div>
                        <div class="pull-right">
                            <a id="glyphicon-help" href="#help" data-toggle="modal" title="Need help?" class="glyphicon glyphicon-question-sign"></a>
                        </div>
                        <form id="gc-form-login" class="form-inline">
                            {% if logged == 'false' %}
                                <input type="text" id="username" class="form-control input-sm" placeholder="Geocaching Username" required>
                                <input type="password" id="password" class="form-control input-sm" placeholder="Geocaching Password" autocomplete="on" required>
                                <button type="button" data-loading-text="Loading..." data-signout-text="Sign out" class="btn btn-default btn-sm" id="login">Sign in</button>
                            {% else %}
                                <span id="signin">Hello {{ username }}!</span>
                                <button type="button" data-loading-text="Loading..." class="btn btn-default btn-sm" id="login">Sign out</button>
                            {% endif %}
                        </form>
                    </header>

                    <hr />

                    <h3>Your unpublished geocaches</h3>
                </div>
                <div class="col-md-6">
                    <div id="unpublishedCachesBlock">
                        <h4>From your account</h4>
                        <div id="block_select">
                            <span>
                                <input type="checkbox" id="chk_select"> <label for="chk_select">Pick 100 geocaches</label>
                            </span>
                            <input type="range" value="100" min="5" max="500" step="5" name="">
                        </div>

                        <div id="fetching-unpublished-caches" class="well well-sm"><img src="loader.gif" alt="" /> Fetching your unpublished caches...</div>
                        <div id="table-unpublished-caches">
                            <table id="table-caches" class="table table-hover table-condensed">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th class="head-pick"><input type="checkbox" id="select-all" title="Select All" /></th>
                                        <th class="head-title">Title</th>
                                        <th class="head-link">Link</th>
                                        <th class="head-status">Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <thead>
                                    <tr>
                                        <td colspan="5">
                                            <button type="submit" class="btn btn-primary" data-loading-text="Refreshing..." id="refresh-cache"><span class="glyphicon glyphicon-refresh"></span> Refresh the list</button>
                                        </td>
                                    </tr>
                                </thead>
                            </table>

                            <fieldset>
                                <legend>Create your GPX File</legend>

                                <div id="block_split">
                                    <span>
                                        <input type="checkbox" id="chk_split"> <label for="chk_split">Split GPX files by 100 geocaches</label>
                                    </span>
                                    <input type="range" value="100" min="5" max="500" step="5" name="">
                                </div>

                                <button type="submit" class="btn btn-primary" data-loading-text="Creating... 0%" id="create-gpx"><span class="glyphicon glyphicon-ok-circle"></span> Create GPX</button>
                                <ul id="download-links"></ul>
                            </fieldset>
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
                                        <th class="head-link">Link</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <button type="submit" class="btn btn-primary" data-loading-text="Refreshing..." id="refresh-cache-gm"><span class="glyphicon glyphicon-refresh"></span> Refresh the list</button>
                            <button type="submit" class="btn btn-primary" data-loading-text="Creating..." id="create-gpx-gm"><span class="glyphicon glyphicon-ok-circle"></span> Create GPX</button>
                            <p id="download-links-gm"></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <script src="js/jquery.min.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
        <script src="js/app.min.js?{{ constant('SUFFIX_CSS_JS') }}"></script>

        <script type="text/javascript">
            require('unpublished.js');
        </script>
    </body>
</html>
