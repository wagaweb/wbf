let pkg = require('../package.json');

module.exports = {
    slug: 'wbf',
    wbf_version: pkg.version,
    filenames: {
        main_js: "wbf-admin.js",
        bundle_js: "wbf-admin.js"
    },
    paths: {
        //Tasks:
        tasks: './build-configs/',
        //Scripts:
        scripts: ['./assets/src/js/**/*.js'],
        main_js: ['./assets/src/js/wbf-admin.js'],
        bundle_js: ['./assets/dist/js/wbf-admin.js'],
        //Styles:
        main_admin_style: './assets/src/scss/dashboard.scss',
        styles: './assets/src/scss/**/*.scss',
        //Components:
        components_dir: "./src/components",
        //Build:
        build_dir: "./builds",
        build_pattern: [
            "cache/wbf_font_cache.json",
            "assets/**",
            "src/**",
            "*.*",
            "!.*" ,
            "!gulpfile.js",
            "!package.json",
            "!bower.json",
            "!{builds,builds/**}",
            "!{builds-configs,builds-configs/**}",
            "!{node_modules,node_modules/**}",
            "!{bower_components,bower_components/**}",
            //Vendors
            "vendor/owl.carousel/**/*",
            "vendor/yahnis-elsts/**/*",
            "vendor/codemirror/**/*",
            "vendor/spectrum/**/*",
            "vendor/options-framework/**/*",
            "vendor/mobiledetect/**/*",
            "vendor/mgargano/**/*",
            "vendor/myclabs/**/*",
            "vendor/composer/**/*",
            "vendor/autoload.php"
        ]
    },
    'available_components': [
        'assets',"breadcrumb","compiler","customupdater","license","mvc","navwalker","notices","pluginsframework","utils"
    ]
};