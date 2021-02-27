module.exports = function( grunt ) {
	'use strict';

	// Load all grunt tasks matching the `grunt-*` pattern
	require( 'load-grunt-tasks' )( grunt );

	// Show elapsed time
	require( 'time-grunt' )( grunt );

	// Project configuration
	grunt.initConfig(
		{
			package : grunt.file.readJSON( 'package.json' ),
			dirs    : {
				lang : 'src/languages',
				code : 'src'
			},

			makepot : {
				dist : {
					options : {
						cwd             : '<%= dirs.code %>',
						domainPath      : 'languages',
						exclude         : [],
						potFilename     : 'dws-wp-framework-bootstrapper.pot',
						mainFile        : 'bootstrap.php',
						potHeaders      : {
							'report-msgid-bugs-to'  : 'https://github.com/deep-web-solutions/wordpress-framework-bootstrapper/issues',
							'project-id-version'    : '<%= package.title %> <%= package.version %>',
							'poedit'     		    : true,
							'x-poedit-keywordslist' : true,
						},
						processPot      : function( pot ) {
							delete pot.headers['x-generator'];

							// include the default value of the constant DWS_WP_FRAMEWORK_WHITELABEL_NAME
							pot.translations['']['DWS_WP_FRAMEWORK_WHITELABEL_NAME'] = {
								msgid: 'Deep Web Solutions',
								comments: { reference: 'bootstrap-whitelabel.php:8' },
								msgstr: [ '' ]
							};

							// include the default value of the constant DWS_WP_FRAMEWORK_BOOTSTRAPPER_NAME
							pot.translations['']['DWS_WP_FRAMEWORK_BOOTSTRAPPER_NAME'] = {
								msgid: 'Deep Web Solutions: Framework Bootstrapper',
								comments: { reference: 'bootstrap.php:38' },
								msgstr: [ '' ]
							};

							return pot;
						},
						type            : 'wp-plugin',
						updateTimestamp : false,
						updatePoFiles   : true
					}
				}
			},
			potomo  : {
				dist : {
					options : {
						poDel : false
					},
					files : [ {
						expand: true,
						cwd: '<%= dirs.lang %>',
						src: [ '*.po' ],
						dest: '<%= dirs.lang %>',
						ext: '.mo',
						nonull: true
					} ]
				}
			}
		}
	);

	grunt.registerTask( 'i18n', ['makepot', 'potomo'] );
}
