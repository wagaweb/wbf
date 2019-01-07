const gulp = require('gulp'),
    bower = require('gulp-bower'),
    runSequence  = require('run-sequence');

const configs = require('./build-configs/configs.js');
require(configs.paths.tasks + 'stylesheets.js')(gulp,configs);
require(configs.paths.tasks + 'scripts.js')(gulp,configs);
require(configs.paths.tasks + 'vendors.js')(gulp,configs);
require(configs.paths.tasks + 'build.js')(gulp,configs);

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
    runSequence('setup', 'watch', callback);
});