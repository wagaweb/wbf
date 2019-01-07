var gcopy = require('gulp-copy'),
    zip = require('gulp-zip'),
    sort = require('gulp-sort'),
    wpPot = require('gulp-wp-pot');

module.exports = (gulp,configs) => {
    /**
     * Creates the theme package
     */
    gulp.task('make-package', function(){
        return gulp.src(configs.configs.paths.build_pattern)
            .pipe(gcopy(configs.paths.build_dir+"/pkg/"+configs.slug));
    });

    /**
     * Compress che package directory
     */
    gulp.task('archive', function(){
        return gulp.src(configs.paths.build_dir+"/pkg/**")
            .pipe(zip(configs.slug+'-'+configs.wbf_version+'.zip'))
            .pipe(gulp.dest(configs.paths.build_dir));
    });

    /**
     * Make the pot file
     */
    gulp.task('make-pot', function () {
        return gulp.src(['*.php', 'src/**/*.php'])
            .pipe(sort())
            .pipe(wpPot( {
                domain: configs.slug,
                destFile: configs.slug+'.pot',
                team: 'Waga Team <dev@waga.it>'
            } ))
            .pipe(gulp.dest('languages/'));
    });
};