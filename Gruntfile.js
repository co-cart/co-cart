/**
 * Build automation scripts.
 * 
 * @package CoCart
 */

module.exports = function(grunt) {
	var sass = require( 'sass' );
	require( 'load-grunt-tasks' )( grunt );
	grunt.loadNpmTasks('grunt-shell');

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON( 'package.json' ),

		// Setting folder templates.
		dirs: {
			css: 'assets/css',
			js: 'assets/js',
			php: 'includes',
			scss: 'assets/scss'
		},

		// SASS to CSS
		sass: {
			compile: {
				options: {
					implementation: sass,
					sourcemap: 'none'
				},
				files: [
					{
						expand: true,
						cwd: '<%= dirs.scss %>/admin/',
						src: [
							'**/*.scss',
							'!parts/*.scss', // Exclude partials.
						],
						dest: '<%= dirs.css %>/admin/',
						ext: '.css'
					},
				],
			}
		},

		// Generate RTL .css files.
		rtlcss: {
			dist: {
				expand: true,
				src: [
					'<%= dirs.css %>/admin/*.css',
					'!<%= dirs.css %>/admin/*-rtl.css',
					'!<%= dirs.css %>/admin/*.min.css'
				],
				ext: '-rtl.css'
			}
		},

		// Post CSS
		postcss: {
			options: {
				processors: [
					require( 'autoprefixer' )
				]
			},
		dist: {
				src: [
					'!<%= dirs.css %>/admin/*.min.css',
					'<%= dirs.css %>/admin/*.css'
				]
			}
		},

		// Minify JavaScript
		uglify: {
			options: {
				banner: '/*! <%= pkg.title %> v<%= pkg.version %> <%= grunt.template.today("dddd dS mmmm yyyy HH:MM:ss TT Z") %> */',
				parse: {
					strict: false
				},
				output: {
					comments : /@license|@preserve|^!/
				}
			},
			build: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/admin',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dirs.js %>/admin',
					ext: '.min.js'
				}]
			}
		},

		// Minify CSS
		cssmin: {
			options: {
				processImport: false,
				roundingPrecision: -1,
				shorthandCompacting: false
			},
			target: {
				files: [{
					expand: true,
					src: [
						'<%= dirs.css %>/admin/*.css',
						'!<%= dirs.css %>/admin/*.min.css'
					],
					ext: '.min.css'
				}]
			}
		},

		// Check for Javascript errors.
		jshint: {
			options: {
				reporter: require('jshint-stylish'),
				globals: {
					"EO_SCRIPT_DEBUG": false,
				},
				'-W099': true, // Mixed spaces and tabs
				'-W083': true, // Fix functions within loop
				'-W082': true, // Declarations should not be placed in blocks
				'-W020': true, // Read only - error when assigning EO_SCRIPT_DEBUG a value.
			},
			all: [
				'<%= dirs.js %>/admin/*.js',
				'!<%= dirs.js %>/admin/*.min.js'
			]
		},

		// Watch changes in assets.
		watch: {
			css: {
				files: [
					'<%= dirs.scss %>/*.scss',
					'<%= dirs.scss %>/admin/*.scss',
				],
				tasks: ['sass', 'stylelint']
			},
			js: {
				files: [
					'<%= dirs.js %>/admin/*.js',
					'!<%= dirs.js %>/admin/*.min.js',
				],
				tasks: ['jshint', 'uglify']
			}
		},

		// Check for Sass errors with "stylelint"
		stylelint: {
			options: {
				configFile: '.stylelintrc'
			},
			all: [
				'<%= dirs.scss %>/**/*.scss',
			]
		},

		// Download translations
		glotpress_download: {
			stable: {
				options: {
					domainPath: 'languages',
					url: 'https://translate.cocartapi.com',
					slug: '<%= pkg.name %>',
				}
			},
			development: {
				options: {
					domainPath: 'languages',
					url: 'https://translate.cocartapi.com',
					slug: '<%= pkg.name %>/development',
				}
			},
		},

		// Generate .pot file
		makepot: {
			target: {
				options: {
					cwd: '',
					domainPath: 'languages', // Where to save the POT file.
					exclude: [ // List of files or directories to ignore.
						'.wordpress-org',
						'releases',
						'node_modules',
						'vendor'
					],
					mainFile: '<%= pkg.name %>.php', // Main project file.
					potComments: 'Copyright (c) {year} CoCart Headless, LLC\nThis file is distributed under the same license as the CoCart package.', // The copyright at the beginning of the POT file.
					potFilename: '<%= pkg.name %>.pot', // Name of the POT file.
					potHeaders: {
						'poedit': true, // Includes common Poedit headers.
						'x-poedit-keywordslist': true, // Include a list of all possible gettext functions.
						'Report-Msgid-Bugs-To': 'https://github.com/co-cart/co-cart/issues',
						'language-team': 'CoCart Headless, LLC <support@cocartapi.com>',
						'language': 'en_US'
					},
					processPot: function( pot ) {
						var translation,
						excluded_meta = [
							'Plugin Name of the plugin/theme',
							'Plugin URI of the plugin/theme',
							'Description of the plugin/theme',
							'Author of the plugin/theme',
							'Author URI of the plugin/theme'
						];

						for ( translation in pot.translations[''] ) {
							if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
								if ( excluded_meta.indexOf( pot.translations[''][ translation ].comments.extracted ) >= 0 ) {
									console.log( 'Excluded meta: ' + pot.translations[''][ translation ].comments.extracted );
									delete pot.translations[''][ translation ];
								}
							}
						}

						return pot;
					},
					type: 'wp-plugin',                                        // Type of project.
					updateTimestamp: true,                                    // Whether the POT-Creation-Date should be updated without other changes.
				}
			}
		},

		// Check strings for localization issues
		checktextdomain: {
			options:{
				text_domain: '<%= pkg.name %>', // Project text domain.
				updateDomains: [ // List of text domains to replace should they be incorrect.
					'co-cart',
					'meshpress',
					'woocommerce'
				],
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					'wp_set_script_translations:1,2d,3'
				]
			},
			files: {
				src:  [
					'*.php',
					'**/*.php', // Include all files
					'!.wordpress-org/**', // Exclude .wordpress-org/
					'!node_modules/**', // Exclude node_modules/
					'!vendor/**' // Exclude vendor/
				],
				expand: true
			},
		},

		// Bump version numbers (replace with version in package.json)
		replace: {
			php: {
				src: [
					'<%= pkg.name %>.php',
					'<%= dirs.php %>/class-cocart.php'
				],
				overwrite: true,
				replacements: [
					{
						from: /Plugin Name:.*$/m,
						to: "Plugin Name: <%= pkg.title %>"
					},
					{
						from: /Description:.*$/m,
						to: "Description: <%= pkg.description %>"
					},
					{
						from: /Requires at least:.*$/m,
						to: "Requires at least: <%= pkg.requires %>"
					},
					{
						from: /Requires PHP:.*$/m,
						to: "Requires PHP: <%= pkg.requires_php %>"
					},
					{
						from: /Tested up to:.*$/m,
						to: 'Tested up to: <%= pkg.tested_up_to %>'
					},
					{
						from: /WC requires at least:.*$/m,
						to: "WC requires at least: <%= pkg.wc_requires %>"
					},
					{
						from: /WC tested up to:.*$/m,
						to: "WC tested up to: <%= pkg.wc_tested_up_to %>"
					},
					{
						from: /Version:.*$/m,
						to: "Version:     <%= pkg.version %>"
					},
					{
						from: /public static \$version = \'.*.'/m,
						to: "public static $version = '<%= pkg.version %>'"
					},
					{
						from: /public static \$tested_up_to_wp = \'.*.'/m,
						to: "public static $tested_up_to_wp = '<%= pkg.tested_up_to %>'"
					},
					{
						from: /public static \$required_wp = \'.*.'/m,
						to: "public static $required_wp = '<%= pkg.requires %>'"
					},
					{
						from: /public static \$required_woo = \'.*.'/m,
						to: "public static $required_woo = '<%= pkg.wc_requires %>'"
					},
					{
						from: /public static \$required_php = \'.*.'/m,
						to: "public static $required_php = '<%= pkg.requires_php %>'"
					}
				]
			},
			readme: {
				src: [
					'readme.txt',
				],
				overwrite: true,
				replacements: [
					{
						from: /Requires at least:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/mi,
						to: 'Requires at least:$1$2<%= pkg.requires %>$3'
					},
					{
						from: /Requires PHP:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/mi,
						to: 'Requires PHP:$1$2<%= pkg.requires_php %>$3'
					},
					{
						from: /Tested up to:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/mi,
						to: 'Tested up to:$1$2<%= pkg.tested_up_to %>$3'
					},
					{
						from: /WC requires at least:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/mi,
						to: 'WC requires at least:$1$2<%= pkg.wc_requires %>$3'
					},
					{
						from: /WC tested up to:(\*\*|)(\s*?)[a-zA-Z0-9.-]+(\s*?)$/mi,
						to: 'WC tested up to:$1$2<%= pkg.wc_tested_up_to %>$3'
					},
				]
			},
			stable: {
				src: [
					'readme.txt',
				],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/mi,
						to: 'Stable tag:$1$2<%= pkg.version %>$3'
					},
				]
			},
			package: {
				src: [
					'load-package.php',
				],
				overwrite: true,
				replacements: [
					{
						from: /@version .*$/m,
						to: "@version <%= pkg.version %>"
					},
				]
			}
		},

		// Copies the plugin to create deployable plugin.
		copy: {
			build: {
				files: [
					{
						expand: true,
						src: [
							'**',
							'!.*',
							'!**/*.{dist,gif,html,jpg,jpeg,js,json,log,lock,md,md5,neon,png,scss,sh,txt,xml,yml,zip}',
							'!.*/**',
							'!.DS_Store',
							'!.htaccess',
							'assets/js/**',
							'assets/images/**',
							'!assets/scss/**',
							'!assets/**/*.scss',
							'!bin/**',
							'!<%= pkg.name %>-git/**',
							'!<%= pkg.name %>-svn/**',
							'!node_modules/**',
							'!releases/**',
							'!tests/**',
							'!vendor/**',
							'!unit-tests/**',
							'readme.txt'
						],
						dest: 'build/',
						dot: true
					}
				]
			},
			firebuild: {
				files: [
					{
						expand: true,
						src: [
							'**',
							'!node_modules/**',
							'!releases/**',
							'!tests/**',
							'!vendor/**',
							'!unit-tests/**',
						],
						dest: 'fire-build/',
						dot: true
					}
				]
			}
		},

		// Compresses the deployable plugin folder.
		compress: {
			zip: {
				options: {
					archive: './releases/<%= pkg.name %>-v<%= pkg.version %>.zip',
					mode: 'zip'
				},
				files: [
					{
						expand: true,
						cwd: './build/',
						src: '**',
						dest: '<%= pkg.name %>'
					}
				]
			},
			firebuild: {
				options: {
					archive: './releases/fire-builds/<%= pkg.name %>-v<%= pkg.version %>-<%= grunt.template.today("dS-mmmm-yyyy-HH-MM") %>.zip',
					mode: 'zip'
				},
				files: [
					{
						expand: true,
						cwd: './fire-build/',
						src: '**',
						dest: ''
					}
				]
			},
			tarGz: {
				options: {
					archive: './releases/<%= pkg.name %>-v<%= pkg.version %>.tar.gz',
					mode: 'tgz' // Setting the mode to 'tgz' will create a .tar.gz archive
				},
				files: [
					{
						expand: true,
						cwd: './build/',
						src: '**',
						dest: '<%= pkg.name %>'
					}
				]
			}
		},

		// Deletes the deployable plugin folder once zipped up.
		clean: {
			build: [ 'build/' ],
			firebuild: [ 'fire-build/' ],
			checksum: ['checksum.md5']
		},

		// Shell task to generate the checksum file with exclusions
		shell: {
			generateChecksum: {
				command: `find . -type f \
					! -path '*/.*' \
					! -name '*.dist' \
					! -name '*.gif' \
					! -name '*.html' \
					! -name '*.jpg' \
					! -name '*.jpeg' \
					! -name '*.js' \
					! -name '*.json' \
					! -name '*.log' \
					! -name '*.lock' \
					! -name '*.md' \
					! -name '*.png' \
					! -name '*.scss' \
					! -name '*.sh' \
					! -name '*.txt' \
					! -name '*.xml' \
					! -name '*.zip' \
					! -name '*.md5' \
					! -name '*.neon' \
					! -path '*/assets/scss/*' \
					! -path '*/bin/*' \
					! -path '*/node_modules/*' \
					! -path '*/releases/*' \
					! -path '*/tests/*' \
					! -path '*/vendor/*' \
					! -path '*/unit-tests/*' \
					-exec md5sum {} + > checksum.md5`
			}
		}

	}); // END of Grunt modules.

	// Set the default grunt command to run test cases.
	grunt.registerTask( 'default', [ 'check' ] );

	// Checks for errors.
	grunt.registerTask( 'check', [ 'stylelint', 'jshint', 'checktextdomain' ] );

	// Build CSS ONLY!
	grunt.registerTask( 'css', [ 'sass', 'rtlcss', 'postcss', 'cssmin' ] );

	// Build JS ONLY!
	grunt.registerTask( 'js', [ 'jshint', 'uglify' ] );

	// Update version of plugin and package.
	grunt.registerTask( 'version', [ 'replace:php', 'replace:readme', 'replace:package' ] );

	// Update stable version of plugin in readme.txt.
	grunt.registerTask( 'stable', [ 'replace:stable' ] );

	/**
	 * Run i18n related tasks.
	 *
	 * This includes extracting translatable strings, updating the master pot file.
	 * If this is part of a deploy process, it should come before zipping everything up.
	 */
	grunt.registerTask( 'get-translations', [ 'glotpress_download:stable' ] );
	grunt.registerTask( 'get-translations:dev', [ 'glotpress_download:development' ] );
	grunt.registerTask( 'update-pot', [ 'checktextdomain', 'makepot' ] );

	/**
	 * Creates a deployable plugin zipped up ready to upload
	 * and install on a WordPress installation.
	 */
	grunt.registerTask( 'zip-only', [ 'copy:build', 'compress:zip', 'clean:build' ] );
	grunt.registerTask( 'tar-only', [ 'copy:build', 'compress:tarGz', 'clean:build' ] );

	// Backup a copy of everything incase of emergency.
	grunt.registerTask( 'zipfire', [ 'copy:firebuild', 'compress:firebuild', 'clean:firebuild' ] );

	// Build Plugin.
	grunt.registerTask( 'compress-all', [ 'copy:build', 'compress:zip', 'compress:tarGz', 'clean:build' ] );
	grunt.registerTask( 'build', [ 'version', 'css', 'js', 'update-pot', 'compress-all' ] );
	grunt.registerTask( 'fire', [ 'version', 'css', 'js', 'update-pot', 'zipfire' ] );
	grunt.registerTask( 'clear', [ 'clean:build', 'clean:firebuild' ] );

	// Ready for release.
	grunt.registerTask( 'ready', [ 'version', 'stable', 'css', 'js', 'update-pot', 'compress-all' ] );

	// Register a custom task for generating the checksum
	grunt.registerTask( 'checksum', [ 'clean:checksum', 'shell:generateChecksum' ]);

};
