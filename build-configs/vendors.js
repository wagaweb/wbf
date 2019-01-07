const copy = require('copy');

module.exports = (gulp,configs) => {
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
        copy(['./vendor/spectrum/spectrum.js'],'./assets/src/js',{flatten: true},cb);

        //Copy spectrum css
        copy(['./vendor/spectrum/spectrum.css'],'./assets/src/css',{flatten: true},cb);
    });
};