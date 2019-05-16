module.exports = function(grunt) {
	'use strict';

	require('load-grunt-tasks')(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// Update developer dependencies
		devUpdate: {
			packages: {
				options: {
					packageJson: null,
					packages: {
						devDependencies: true,
						dependencies: false
					},
					reportOnlyPkgs: [],
					reportUpdated: false,
					semver: true,
					updateType: 'force'
				}
			}
		},

		// Generate .pot file
		makepot: {
			target: {
				options: {
					cwd: '',
					domainPath: 'languages',                                  // Where to save the POT file.
					exclude: [
						'releases',
						'woo-dependencies/.*',
						'node_modules'
					],
					mainFile: '<%= pkg.name %>.php', // Main project file.
					potComments: '# Copyright (c) {{year}} Sébastien Dumont', // The copyright at the beginning of the POT file.
					domainPath: 'languages', // Where to save the POT file.
					potFilename: '<%= pkg.name %>.pot', // Name of the POT file.
					potHeaders: {
						'poedit': true,                                       // Includes common Poedit headers.
						'x-poedit-keywordslist': true,                        // Include a list of all possible gettext functions.
						'Report-Msgid-Bugs-To': 'https://github.com/co-cart/co-cart/issues',
						'language-team': 'Sébastien Dumont <mailme@sebastiendumont.com>',
						'language': 'en_US'
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
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src:  [
					'*.php',
					'**/*.php', // Include all files
					'!woo-dependencies/**', // Exclude woo-dependencies/
					'!node_modules/**' // Exclude node_modules/
				],
				expand: true
			},
		},

		potomo: {
			dist: {
				options: {
					poDel: false
				},
				files: [{
					expand: true,
					cwd: 'languages',
					src: ['*.po'],
					dest: 'languages',
					ext: '.mo',
					nonull: false
				}]
			}
		},

		// Bump version numbers (replace with version in package.json)
		replace: {
			php: {
				src: [ '<%= pkg.name %>.php' ],
				overwrite: true,
				replacements: [
					{
						from: /Description:.*$/m,
						to: "Description: <%= pkg.description %>"
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
					}
				]
			},
			readme: {
				src: [
					'readme.txt',
					'README.md'
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
						from: /Stable tag:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/mi,
						to: 'Stable tag:$1$2<%= pkg.version %>$3'
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
							'!**/*.{gif,jpg,jpeg,js,json,log,md,png,scss,sh,txt,xml,zip}',
					'!.*/**',
							'!.DS_Store',
					'!.htaccess',
							'!assets/scss/**',
							'!assets/**/*.scss',
							'!<%= pkg.name %>-git/**',
							'!<%= pkg.name %>-svn/**',
							'!node_modules/**',
					'!releases/**',
							'readme.txt'
				],
						dest: 'build/',
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
			}
		},

		// Deletes the deployable plugin folder once zipped up.
		clean: {
			build: [ 'build/' ]
		}
	});

	// Set the default grunt command to run test cases.
	grunt.registerTask( 'default', [ 'test' ] );

	// Checks for developer dependencie updates.
	grunt.registerTask( 'check', [ 'devUpdate' ] );

	// Checks for errors.
	grunt.registerTask( 'test', [ 'checktextdomain' ]);

	// Update version of plugin.
	grunt.registerTask( 'version', [ 'replace' ] );

	/**
	 * Run i18n related tasks.
	 *
	 * This includes extracting translatable strings, updating the master pot file.
	 * If this is part of a deploy process, it should come before zipping everything up.
	 */
	grunt.registerTask( 'update-pot', [ 'checktextdomain', 'makepot' ]);

	/**
	 * Creates a deployable plugin zipped up ready to upload
	 * and install on a WordPress installation.
	 */
	grunt.registerTask( 'zip', [ 'copy:build', 'compress', 'clean:build' ]);
};
