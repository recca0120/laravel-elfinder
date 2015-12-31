<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>elFinder 2.0</title>

		<!-- jQuery and jQuery UI (REQUIRED) -->
		<link rel="stylesheet" type="text/css" href="{{ asset('vendor/elfinder/jquery/ui-themes/smoothness/jquery-ui-1.10.1.custom.min.css') }}">
		<script src="{{ asset('vendor/elfinder/jquery/jquery-1.9.1.min.js') }}"></script>
		<script src="{{ asset('vendor/elfinder/jquery/jquery-ui-1.10.1.custom.min.js') }}"></script>

		<!-- elFinder CSS (REQUIRED) -->
		<link rel="stylesheet" type="text/css" href="{{ asset('vendor/elfinder/css/elfinder.min.css') }}">
		<link rel="stylesheet" type="text/css" href="{{ asset('vendor/elfinder/css/theme.css') }}">
		<!-- elFinder JS (REQUIRED) -->
		<script src="{{ asset('vendor/elfinder/js/elfinder.min.js') }}"></script>

		<!-- elFinder initialization (REQUIRED) -->
		<script>
            $(document).on("ready", function() {
                var getLang = function() {
                    try {
                        var full_lng;
                        var loct = window.location.search;
                        var locm;
                        if (loct && (locm = loct.match(/lang=([a-zA-Z_-]+)/))) {
                            full_lng = locm[1];
                        } else {
                            full_lng = (navigator.browserLanguage || navigator.language || navigator.userLanguage);
                        }
                        var lng = full_lng.substr(0,2);
                        if (lng == 'ja') lng = 'jp';
                        else if (lng == 'pt') lng = 'pt_BR';
                        else if (lng == 'zh') lng = (full_lng.substr(0,5) == 'zh-cn')? 'zh_CN' : 'zh_TW';

                        if (lng != 'en') {
                            var script_tag = document.createElement("script");
                            script_tag.type = "text/javascript";
                            script_tag.src = "{{ asset('vendor/elfinder/js/i18n/elfinder') }}."+lng+".js";
                            script_tag.charset = "utf-8";
                            $("head").append(script_tag);
                        }

                        return lng;
                    } catch(e) {
                        return 'en';
                    }
                };

                var FileBrowserDialogue = {
                    init: function() {
                        // Here goes your code for setting your custom things onLoad.
                    },
                    mySubmit: function (file, fm) {
                        // pass selected file data to TinyMCE
                        parent.tinymce.activeEditor.windowManager.getParams().oninsert(file, fm);
                        // close popup window
                        parent.tinymce.activeEditor.windowManager.close();
                    }
                }


                var elfinderInstance = $("#elfinder").elfinder({
                    resizable: false,
                    height: $(window).height() - 20,
                    url: "{{ route('elfinder::connector') }}",
                    lang: getLang(),
                    customData: {
                        _token: '<?php echo csrf_token() ?>'
                    },
                    getFileCallback: function(file) { // editor callback
                        // file.url - commandsOptions.getfile.onlyURL = false (default)
                        // file     - commandsOptions.getfile.onlyURL = true
                        if (parent.tinymce) {
                            FileBrowserDialogue.mySubmit(file, elfinderInstance); // pass selected file path to TinyMCE
                        }
                    }
                }).elfinder('instance');

                // set document.title dynamically etc.
                var title = document.title;
                elfinderInstance.bind('open', function(event) {
                    var data = event.data || null;
                    var path = '';

                    if (data && data.cwd) {
                        path = elfinderInstance.path(data.cwd.hash) || null;
                    }
                    document.title =  path? path + ':' + title : title;
                });

                var resizeTimer = null;
                $(window).on("resize", function() {
                    resizeTimer && clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        var h = parseInt($(window).height()) - 20;
                        if (h != parseInt($('#elfinder').height())) {
                            elfinderInstance.resize('100%', h);
                        }
                    }, 200);
                });
            });
		</script>
	</head>
	<body>

		<!-- Element where elFinder will be created (REQUIRED) -->
		<div id="elfinder" style="height:100%;"></div>

	</body>
</html>
