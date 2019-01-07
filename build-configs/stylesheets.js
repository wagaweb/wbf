const rename = require("gulp-rename"),
    sass = require('gulp-sass'),
    postcss = require('gulp-postcss'),
    sourcemaps = require('gulp-sourcemaps'),
    cssnano = require('cssnano'),
    autoprefixer = require('autoprefixer'),
    merge  = require('merge-stream');

module.exports = (gulp,configs) => {
    /**
     * Compile less files
     */
    gulp.task('compile_css',function(){
        var processors = [
            autoprefixer({browsers: ['last 1 version']}),
            cssnano()
        ];

        var styles = gulp.src(configs.paths.main_admin_style)
            .pipe(sourcemaps.init())
            .pipe(sass()).on('error',sass.logError)
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
};