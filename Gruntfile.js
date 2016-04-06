module.exports = function (grunt) {

    // load all tasks
    require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev: {
                options: {},
                files: {
                    'admin/css/tinymce.css': 'assets/src/less/tinymce.less',
                    'admin/css/admin.css': 'assets/src/less/admin.less',
                    'admin/css/optionsframework.css': 'assets/src/less/optionsframework.less',
                    'admin/css/componentsframework.css': 'assets/src/less/componentsframework.less',
                    'admin/css/pagebuilder.css': 'assets/src/less/pagebuilder.less'
                }
            },
            production: {
                options: {
                    cleancss: true
                },
                files: ['<%= less.dev.files %>']
            }
        },
        jshint: {
            all: ['assets/src/js/**/*.js'],
            options: {
                browser: true,
                curly: false,
                eqeqeq: false,
                eqnull: true,
                expr: true,
                immed: true,
                newcap: true,
                noarg: true,
                smarttabs: true,
                sub: true,
                undef: false
            }
        },
        jsbeautifier: {
            files: ['admin/js/*.js', 'public/js/*.js','includes/scripts/*.js','includes/scripts/**/*.js'],
            options: {}
        },
        browserify: {
            dist: {
                src: ['assets/src/js/admin/wbf-admin.js'],
                dest: 'assets/src/js/admin/wbf-admin-bundle.js'
            }
        },
        uglify: {
            options: {
                // the banner is inserted at the top of the output
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    'admin/js/wbf-admin.min.js': ['assets/src/js/admin/wbf-admin-bundle.js'],
                    /*'admin/js/admin.min.js': ['assets/src/js/admin/admin.js'],
                    'admin/js/acf-fields.min.js': ['assets/src/js/admin/acf-fields/*.js'],
                    'admin/js/code-editor.min.js': ['assets/src/js/admin/code-editor.js'],
                    'admin/js/components-page.min.js': ['assets/src/js/admin/components-page.js'],
                    'admin/js/font-selector.min.js': ['assets/src/js/admin/font-selector.js'],*/
                    'includes/scripts/wbfgmap.min.js': ['assets/src/js/includes/wbfgmap/markerclusterer.js','assets/src/js/includes/wbfgmap/acfmap.js']
                }
            }
        },
        pot: {
            options: {
                text_domain: 'wbf',
                dest: 'languages/',
                keywords: [
                    '__:1',
                    '_e:1',
                    '_x:1,2c',
                    'esc_html__:1',
                    'esc_html_e:1',
                    'esc_html_x:1,2c',
                    'esc_attr__:1',
                    'esc_attr_e:1',
                    'esc_attr_x:1,2c',
                    '_ex:1,2c',
                    '_n:1,2',
                    '_nx:1,2,4c',
                    '_n_noop:1,2',
                    '_nx_noop:1,2,3c'
                ]
            },
            files: {
                src: ['*.php','admin/**/*.php','includes/**/*.php','public/**/*.php'],
                expand: true
            }
        },
        copy: {
            dist: {
                files: [
                    {
                        expand: true,
                        cwd: "./",
                        src: [
                            "**/*",
                            "!.*",
                            "!Gruntfile.js",
                            "!package.json",
                            "!composer.json",
                            "!composer.lock",
                            "!.jshintrc",
                            "!.bowerrc",
                            "!bower.json",
                            "!builds/**",
                            "!bin/**",
                            "!tests/**",
                            "!node_modules/**",
                            "!bower_components/**",
                            "!assets/cache/**",
                            "!vendor/**",
                            "vendor/composer/*.php",
                            "vendor/composer/*.json",
                            "vendor/acf/**/*",
                            "!vendor/acf/lang/*",
                            "vendor/codemirror/lib/*",
                            "vendor/imagesloaded/*.js",
                            "vendor/jquery-modal/*.js",
                            "vendor/mgargano/simplehtmldom/src/*.*",
                            "vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php",
                            "vendor/options-framework/**/*",
                            "vendor/owlcarousel/**/*",
                            "vendor/theme-updates/**/*",
                            "vendor/yahnis-elsts/**/*",
                            "vendor/autoload.php",
                            "vendor/BootstrapNavMenuWalker.php",
                            "vendor/breadcrumb-trail.php",
                            "!_bak/**"
                        ],
                        dest: "builds/wbf-<%= pkg.version %>/"
                    }
                ]
            }
        },
        compress: {
            build: {
                options: {
                    archive: "builds/wbf-<%= pkg.version %>.zip"
                },
                files: [
                    {
                        expand: true,
                        cwd: "./",
                        src: '<%= copy.dist.files.0.src %>',
                        dest: "wbf/"
                    }
                ]
            }
        },
        watch: {
            less: {
                files: 'assets/src/less/*.less',
                tasks: ['less:dev']
            }/*,
            scripts: {
                files: ['<%= jshint.all %>'],
                task: ['jshint']
            }*/
        }
    });

    /*
     *  Register tasks
     */

    //Default task
    grunt.registerTask('default', ['watch']);

    //Setup task
    grunt.registerTask('setup', ['bower-install', 'jsmin', 'less:dev']);

    //Concat and beautify js
    grunt.registerTask('js', ['jsbeautifier','browserify:dist']);

    //Concat, beautify and minify js
    grunt.registerTask('jsmin', ['js', 'uglify']);

    //Build task
    grunt.registerTask('build', ['bower-update','less:production', 'jsmin', 'compress:build']);

    //Runs bower install
    grunt.registerTask('bower-install', function() {
        var exec = require('child_process').exec;
        var cb = this.async();
        exec('bower install', function(err, stdout, stderr) {
            console.log(stdout);
            cb();
        });
    });

    //Runs bower update
    grunt.registerTask('bower-update', function() {
        var exec = require('child_process').exec;
        var cb = this.async();
        exec('bower update', function(err, stdout, stderr) {
            console.log(stdout);
            cb();
        });
    });
};