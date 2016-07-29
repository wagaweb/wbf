module.exports = function (grunt) {
    
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev: {
                options: {},
                files: {
                    'assets/dist/css/admin.css': 'assets/src/less/admin.less',
                    'assets/dist/css/optionsframework.css': 'assets/src/less/optionsframework.less',
                    'assets/dist/css/componentsframework.css': 'assets/src/less/componentsframework.less'
                }
            },
            production: {
                options: {
                    cleancss: true
                },
                files: ['<%= less.dev.files %>']
            }
        },
        browserify: {
            dist: {
                src: ['assets/src/js/wbf-admin.js'],
                dest: 'assets/dist/js/wbf-admin-bundle.js'
            }
        },
        uglify: {
            options: {
                // the banner is inserted at the top of the output
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    'assets/dist/js/wbf-admin.min.js': ['assets/dist/js/wbf-admin-bundle.js'],
                    'assets/dist/js/includes/wbfgmap.min.js': ['assets/src/js/includes/wbfgmap/markerclusterer.js','assets/src/js/includes/wbfgmap/acfmap.js'],
                    'assets/dist/js/includes/spectrum-min.js': ['vendor/spectrum/spectrum.js']
                }
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
                            "vendor/spectrum/spectrum.css",
                            "vendor/imagesloaded/*.js",
                            "vendor/jquery-modal/*.js",
                            "vendor/mgargano/simplehtmldom/src/*.*",
                            "vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php",
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
     * Load tasks
     */

    grunt.loadNpmTasks('grunt-browserify');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    /*
     *  Register tasks
     */

    //Default task
    grunt.registerTask('default', ['watch']);

    //Setup task
    grunt.registerTask('setup', ['bower-install', 'jsmin', 'less:dev'], 'component-composer-update');

    //Concat and beautify js
    grunt.registerTask('js', ['browserify:dist']);

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

    //Runs composer update for framework components
    grunt.registerTask('component-composer-update',function(){
        var exec = require('child_process').execSync;
        var glob = require('glob');
        var path = require('path');
        var composer_dirs = [
            'src/components/*'
        ];
        var cb = this.async(); //see http://gruntjs.com/creating-tasks
        for(var i = 0, len = composer_dirs.length; i < len; i++){
            glob(composer_dirs[i],function(err,dirs){
                for(var k = 0; k < dirs.length; k++){
                    var cwd = dirs[k];
                    console.log("*** Exec composer into "+cwd);
                    exec('composer update', {cwd: cwd}, function(err, stdout, stderr) {
                        console.log(stdout);
                        cb();
                    });
                }
            })
        }
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