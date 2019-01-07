const rename = require("gulp-rename"),
    sourcemaps = require('gulp-sourcemaps'),
    uglify = require('gulp-uglify'),
    browserify = require('browserify'),
    babelify = require('babelify'),
    merge  = require('merge-stream'),
    source = require('vinyl-source-stream'), //https://www.npmjs.com/package/vinyl-source-stream---
    buffer = require('vinyl-buffer'); //https://www.npmjs.com/package/vinyl-buffer

module.exports = (gulp,configs) => {
    /**
     * Creates and minimize bundle.js into <pluginslug>.min.js
     */
    gulp.task('compile_js', ['browserify'] ,function(){
        let admin_js = gulp.src(configs.paths.bundle_js)
            .pipe(sourcemaps.init())
            .pipe(uglify())
            .pipe(rename({
                suffix: ".min"
            }))
            .pipe(sourcemaps.write('.'))
            .pipe(gulp.dest('./assets/dist/js'));

        let wbfgmap = gulp.src("assets/src/js/includes/wbfgmap/wbf-google-map.js")
            .pipe(sourcemaps.init())
            .pipe(uglify())
            .pipe(rename({
                suffix: ".min"
            }))
            .pipe(sourcemaps.write('.'))
            .pipe(gulp.dest('./assets/dist/js/includes'));

        let spectrum = gulp.src("assets/src/js/spectrum.js")
            .pipe(uglify())
            .pipe(rename({
                suffix: ".min"
            }))
            .pipe(gulp.dest('./assets/dist/js/includes'));

        return merge(admin_js,wbfgmap,spectrum);
    });

    /**
     * Browserify magic! Creates waboot.js
     */
    gulp.task('browserify', function(){
        return browserify(configs.paths.main_js,{
            insertGlobals : true,
            debug: true
        })
            .transform("babelify", {presets: ["env"]}).bundle()
            .pipe(source(configs.filenames.bundle_js))
            .pipe(buffer()) //This might be not required, it works even if commented
            .pipe(gulp.dest('./assets/dist/js'));
    });
};