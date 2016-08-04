var pkg = require('./package.json');

var gulp = require('gulp'),
    concat = require('gulp-concat'),
    rename = require("gulp-rename"),
    sourcemaps = require('gulp-sourcemaps'),
    jsmin = require('gulp-jsmin'),
    uglify = require('gulp-uglify'),
    sass = require('gulp-sass'),
    less = require('gulp-less'),
    browserify = require('browserify'),
    source = require('vinyl-source-stream'), //https://www.npmjs.com/package/vinyl-source-stream
    buffer = require('vinyl-buffer'), //https://www.npmjs.com/package/vinyl-buffer
    babelify = require('babelify'),
    zip = require('gulp-zip'),
    bower = require('gulp-bower'),
    copy = require('copy'),
    csso = require('gulp-csso'),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    cssnano = require('cssnano'),
    runSequence  = require('run-sequence'),
    wpPot = require('gulp-wp-pot'),
    sort = require('gulp-sort'),
    merge  = require('merge-stream'),
    path = require('path'); //Required by gulp-less

var slug = "wbf";

var filenames = {
    main_js: "wbf-admin.js",
    bundle_js: "wbf-admin.js"
};

var paths = {
    //Scripts:
    scripts: ['./assets/src/js/**/*.js'],
    main_js: ['./assets/src/js/'+filenames.main_js],
    bundle_js: ['./assets/dist/js/'+filenames.bundle_js],
    //Styles:
    styles: './assets/src/less/**/*.less',
    //Build:
    build_dir: "./builds",
    build_pattern: [
        "**/*",
        "!.*" ,
        "!Gruntfile.js",
        "!gulpfile.js",
        "!package.json",
        "!bower.json",
        "!Movefile-sample",
        "!{builds,builds/**}",
        "!{node_modules,node_modules/**}",
        "!{bower_components,bower_components/**}"
    ]
};

/**
 * Compile less files
 */
gulp.task('compile_css',function(){
    var processors = [
        autoprefixer({browsers: ['last 1 version']}),
        cssnano()
    ];

    var styles = gulp.src(paths.styles)
        .pipe(sourcemaps.init())
        .pipe(less({
            paths: ["vendor/bootstrap/less"]
        }))
        .pipe(postcss(processors))
        .pipe(rename({
            suffix: ".min"
        }))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest('./assets/dist/css'));

    var spectrum = gulp.src("assets/src/css/spectrum.css")
        .pipe(postcss(processors))
        .pipe(rename({
            suffix: ".min"
        }))
        .pipe(gulp.dest("assets/dist/css"));

    return merge(styles,spectrum);
});

/**
 * Creates and minimize bundle.js into <pluginslug>.min.js
 */
gulp.task('compile_js', ['browserify'] ,function(){
    var admin_js = gulp.src(paths.bundle_js)
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(rename({
            suffix: ".min"
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./assets/dist/js'));

    var spectrum = gulp.src("assets/src/js/spectrum.js")
        .pipe(uglify())
        .pipe(rename({
            suffix: ".min"
        }))
        .pipe(gulp.dest('./assets/dist/js'));

    return merge(admin_js,spectrum);
});

/**
 * Browserify magic! Creates waboot.js
 */
gulp.task('browserify', function(){
    return browserify(paths.main_js,{
        insertGlobals : true,
        debug: true
    })
        .transform("babelify", {presets: ["es2015"]}).bundle()
        .pipe(source(filenames.bundle_js))
        .pipe(buffer()) //This might be not required, it works even if commented
        .pipe(gulp.dest('./assets/dist/js'));
});

/**
 * Creates the theme package
 */
gulp.task('make-package', function(){
    return gulp.src(paths.build_pattern)
        .pipe(copy(paths.build_dir+"/pkg/"+slug));
});

/**
 * Compress che package directory
 */
gulp.task('archive', function(){
    return gulp.src(paths.build_dir+"/pkg/**")
        .pipe(zip(slug+'-'+pkg.version+'.zip'))
        .pipe(gulp.dest("./builds"));
});

/**
 * Make the pot file
 */
gulp.task('make-pot', function () {
    return gulp.src(['*.php', 'src/**/*.php'])
        .pipe(sort())
        .pipe(wpPot( {
            domain: slug,
            destFile: slug+'.pot',
            team: 'Waga <info@waga.it>'
        } ))
        .pipe(gulp.dest('languages/'));
});

/**
 * Copy vendors to destinations
 */
gulp.task('copy-vendors',function() {
    var cb = function(err,files){
        if(err) return console.error(err);
        files.forEach(function(file) {
            console.log("Copied: "+file.relative);
        });
    };

    //@see https://github.com/jonschlinkert/copy/tree/master/examples

    //Copy spectrum js
    copy(['vendor/spectrum/spectrum.js'],'assets/src/js',{flatten: true},cb);

    //Copy spectrum css
    copy(['vendor/spectrum/spectrum.css'],'assets/src/css',{flatten: true},cb);
});

/**
 * Bower vendors Install
 */
gulp.task('bower-install',function(){
    return bower();
});

/**
 * Bower Update
 */
gulp.task('bower-update',function(){
    return bower({cmd: 'update'});
});

/**
 * Gets the plugin ready
 */
gulp.task('setup', function(callback) {
    runSequence('bower-update', 'copy-vendors', ['compile_js', 'compile_css'], callback);
});

/**
 * Creates a build
 */
gulp.task('build', function(callback) {
    runSequence('bower-update', 'copy-vendors',['compile_js', 'compile_css'], 'make-package', 'archive', callback);
});

/**
 * Rerun the task when a file changes
 */
gulp.task('watch', function() {
    gulp.watch(paths.scripts, ['compile_js']);
    gulp.watch(paths.styles, ['compile_css']);
});

/**
 * Default task
 */
gulp.task('default', function(callback){
    runSequence('bower-install', ['compile_js', 'compile_css'], 'watch', callback);
});