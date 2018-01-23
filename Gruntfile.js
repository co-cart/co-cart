var exec = require('promised-exec'),
	path   = require('path');

module.exports = function(grunt) {
	'use strict';

	require('load-grunt-tasks')(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		pluginSlug: '<%= pkg.name %>',
		mainFile: '<%= pkg.name %>.php',
		changelogFile: 'changelog.txt',
		tmpPath: '/tmp/<%= pluginSlug %>',
		gitPath: path.resolve(__dirname),
		deployUrl: 'git@github.com:<%= pkg.author %>/<%= pkg.repository.slug %>.git',
		remoteName: '<%= pkg.repository.slug %>',

		// Setting directories
		dirs: {
			css: 'assets/css',
			js: 'assets/js',
		},

		cssmin: {
			target: {
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>',
					src: [
						'*.css',
						'!*.min.css'
					],
					dest: '<%= dirs.css %>',
					ext: '.min.css'
				}]
			}
		},

		uglify: {
			options: {
				compress: {
					global_defs: {
						"EO_SCRIPT_DEBUG": false
					},
					dead_code: true
				},
				banner: '/*! <%= pkg.title %> <%= pkg.version %> <%= grunt.template.today("yyyy-mm-dd HH:MM") %> */\n'
			},
			build: {
				files: [{
					expand: true, // Enable dynamic expansion.
					src: [ // Actual pattern(s) to match.
						'<%= dirs.js %>/*.js',
						'!<%= dirs.js %>/*.min.js'
					],
					ext: '.min.js', // Destination filepaths will have this extension.
				}]
			}
		},

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
				'<%= dirs.js %>/*.js',
				'!<%= dirs.js %>/*.min.js'
			]
		},

		watch: {
			scripts: {
				files: '<%= dirs.js %>/*.js',
				tasks: ['jshint', 'uglify'],
				options: {
					debounceDelay: 250,
				},
			},
			css: {
				files: '<%= dirs.css %>/*.css',
				tasks: ['css'],
			},
		},

		// Generate .pot file
		makepot: {
			target: {
				options: {
					type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
					domainPath: 'languages', // Where to save the POT file.
					mainFile: '<%= mainFile %>', // Main project file.
					potFilename: '<%= pkg.name %>.pot', // Name of the POT file.
					potHeaders: {
						'Report-Msgid-Bugs-To': 'https://yourdomain.com/',
						'language-team': 'Your Name <youremail@domain.com>',
						'language': 'en_US'
					},
					exclude: [
						'woo-dependencies/.*',
						'node_modules',
						'tests/.*',
						'tmp'
					]
				}
			}
		},

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
					'!node_modules/**', // Exclude node_modules/
					'!tmp/**', // Exclude tmp/
					'!tests/**' // Exclude tests/
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
			Version: {
				src: [
					'readme.txt',
					'<%= pkg.name %>.php'
				],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag:.*$/m,
						to: "Stable tag: <%= pkg.version %>"
					},
					{
						from: /Version:.*$/m,
						to: "Version: <%= pkg.version %>"
					},
					{
						from: /public \$version = \'.*.'/m,
						to: "public $version = '<%= pkg.version %>'"
					}
				]
			}
		},

		// Copies the plugin to create deployable plugin.
		copy: {
			deploy: {
				src: [
					'**',
					'!.*',
					'!*.md',
					'!.*/**',
					'.htaccess',
					'!Gruntfile.js',
					'!package.json',
					'!node_modules/**',
					'!.DS_Store',
					'!npm-debug.log',
					'!*.sh',
					'!*.zip',
					'!*.jpg',
					'!*.jpeg',
					'!*.gif',
					'!*.png'
				],
				dest: '<%= pkg.name %>',
				expand: true,
				dot: true
			}
		},

		// Compresses the deployable plugin folder.
		compress: {
			zip: {
				options: {
					archive: './<%= pkg.name %>-v<%= pkg.version %>.zip',
					mode: 'zip'
				},
				files: [
					{
						expand: true,
						cwd: './<%= pkg.name %>/',
						src: '**',
						dest: 'releases/'
					}
				]
			}
		},

		// Deletes the deployable plugin folder once zipped up.
		clean: [ '<%= pkg.name %>' ]

	});

	// Set the default grunt command to run test cases.
	grunt.registerTask( 'default', [ 'test' ] );

	// Checks for errors.
	grunt.registerTask( 'test', [ 'cssmin', 'jshint', 'checktextdomain' ]);

	// Checks for errors, updates version and runs i18n tasks.
	grunt.registerTask( 'dev', [ 'replace', 'cssmin', 'jshint', 'newer:uglify', 'makepot' ]);

	// All together now.
	grunt.registerTask( 'build', 'The "build" sequence: [Update, Check, Test and Build, MakePot]', function() {
		grunt.log.ok([
			"..........................................",
			"Preparing to build...",
			".........................................."
		].join("\n\n"));

		grunt.task.run([
			'replace',
			'check-git',
			'minify',
			'update-pot',
			'zip'
		]);
	});

	/**
	 * Run checks and minification tasks.
	 *
	 * 1. Checks for any CSS syntax errors and minifies the CSS if error free.
	 * 2. Checks for any JavaScript syntax errors and minifies the JavaScript if error free.
	 */
	grunt.registerTask( 'minify', [ 'cssmin', 'jshint', 'newer:uglify' ]);

	/**
	 * Run Git related check tasks.
	 *
	 * 1. Checks versions to be the same across all files and compares them to the one in
	 * package.json
	 * 2. Makes sure we do not have a tag with the pacakge version released yet.
	 */
	grunt.registerTask( 'check-git', [ 'check-versions', 'check-git-tag' ]);

	/**
	 * Makes sure we do not have a tag with the pacakge version released yet.
	 * Exits if we do.
	 */
	grunt.registerTask( 'check-git-tag', 'Make sure git tag doesn\'t exist yet', function() {
		var done = this.async(),
			version = '<%= pkg.version %>';

		exec( 'git show-ref --tags --quiet --verify -- "refs/tags/' + version + '"' )
			.then( function() {
				grunt.fail.fatal( 'Version ' + version + ' already exists as git tag.', 3 );
			})
			.fail( function() {
				grunt.log.ok( 'No git tag exists for ' + version + '. Let\'s proceed...' );
			})
			.fin(done);
	});

	/**
	 * Checks versions to be the same across all files and compares them to the one in
	 * package.json
	 * - top of plugin-slug.php within WordPress plugin comment
	 * - static property of the plugin object
	 * - changelog.txt latest entry
	 */
	grunt.registerTask( 'check-versions', 'Make sure all versions everywhere are the same', function() {
		var file = grunt.file.read( '<%= mainFile %>', {encoding: 'utf8'} ),
			changelog = grunt.file.read( '<%= changelogFile %>', {encoding: 'utf8'} ),

			// Need the version from the top of the main file
			readmeVersionRegex = /\* Version: ([0-9\.]+)/g,
			readmeVersion = readmeVersionRegex.exec(file)[1],

			// Need the version from the main file in the public static $version property
			versionPropertyRegex = /public static \$version = '([0-9\.]+)';/g,
			versionProperty = versionPropertyRegex.exec(file)[1],

			// Need the version in package.json file = pkg.version
			// Need the version in changelog.txt
			changelogVersionRegex = /\d{4}\.\d{2}\.\d{2} - version ([0-9\.]+)/g,
			changelogVersion = changelogVersionRegex.exec(changelog)[1],
			newVersion = '<%= pkg.version %>',
			releaseRegex = /\d\.\d\.\d-\d+/,
			isRelease = newVersion.match(releaseRegex),
			messages = [],
			failed = false,
			m;

		if ( newVersion != readmeVersion ) {
			messages.push( 'The readme version (' + readmeVersion + ') is not the same as the new: ' + newVersion );
			failed = true;
		}

		if ( newVersion != versionProperty ) {
			messages.push( 'The version property (' + versionProperty + ') is not the same as the new: ' + newVersion );
			failed = true;
		}

		if ( newVersion != changelogVersion ) {
			messages.push( 'The changelog version (' + changelogVersion + ') is not the same as the new: ' + newVersion );
			failed = true;
		}

		if ( failed && null === isRelease ) {
			m = messages.join("\n");
			grunt.fail.fatal("\n" + m, 3);
		}

		grunt.log.ok( "\nVersions match. Let's proceed...\n" );
	});

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
	grunt.registerTask( 'zip', [ 'copy', 'compress', 'clean' ]);
};
