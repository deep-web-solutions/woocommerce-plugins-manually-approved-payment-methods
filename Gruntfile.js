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
						potFilename     : 'dws-mapm-for-woocommerce.pot',
						mainFile        : 'bootstrap.php',
						potHeaders      : {
							'report-msgid-bugs-to'  : 'https://github.com/deep-web-solutions/woocommerce-plugins-manually-approved-payment-methods/issues',
							'project-id-version'    : '<%= package.title %> <%= package.version %>',
							'poedit'     		    : true,
							'x-poedit-keywordslist' : true,
						},
						processPot      : function( pot ) {
							delete pot.headers['x-generator'];

							// include the default value of the constant DWS_WC_MAPM_PLUGIN_NAME
							pot.translations['']['DWS_WC_MAPM_PLUGIN_NAME'] = {
								msgid: 'Deep Web Solutions: Manually Approved Payment Methods for WooCommerce',
								comments: { reference: 'bootstrap.php:64' },
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
