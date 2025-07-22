/* global module, require */
module.exports = function( grunt ) {
	var SOURCE_DIR = './';

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.initConfig(
		{
			eslint: {
				options: {
					overrideConfigFile: '.eslintrc.js'
				},
				grunt: {
					src: [
						'Gruntfile.js'
					]
				},
				core: {
					options: {
						fix: grunt.option( 'fix' )
					},
					src: [
						'gutenberg/*.js',
						'include/*.js',
						'tinymce/*.js',
						'!**/*.min.js'
					],
					filter: function( filepath ) {
						var index,
							file = grunt.option( 'file' );

						// Don't filter when no target file is specified
						if ( ! file ) {
							return true;
						}

						// Normalize filepath for Windows
						filepath = filepath.replace( /\\/g, '/' );
						index    = filepath.lastIndexOf( '/' + file );

						// Match only the filename passed from cli
						if ( filepath === file || -1 !== index && index === filepath.length - ( file.length + 1 ) ) {
							return true;
						}

						return false;
					}
				}
			},
			jshint: {
				options: grunt.file.readJSON( '.jshintrc' ),
				grunt: {
					src: [ 'Gruntfile.js' ]
				},
				core: {
					expand: true,
					cwd: SOURCE_DIR,
					src: [
						'gutenberg/*.js',
						'include/*.js',
						'tinymce/*.js',
						'!**/*.min.js'
					],
					// Limit JSHint's run to a single specified file:
					//
					//    grunt jshint:core --file=filename.js
					//
					// Optionally, include the file path:
					//
					//    grunt jshint:core --file=path/to/filename.js
					//
					filter: function( filepath ) {
						var index,
							file = grunt.option( 'file' );

						// Don't filter when no target file is specified
						if ( ! file ) {
							return true;
						}

						// Normalize filepath for Windows
						filepath = filepath.replace( /\\/g, '/' );
						index    = filepath.lastIndexOf( '/' + file );

						// Match only the filename passed from cli
						if ( filepath === file || -1 !== index && index === filepath.length - ( file.length + 1 ) ) {
							return true;
						}

						return false;
					}
				}
			},
			phpcs: {
				errors: {
					cwd: SOURCE_DIR,
					src: [
						'**/*.php',
						'!**/*.js',
						'!node_modules/**',
						'!plugin-update-checker/**'
					],
					options: {
						bin: '/usr/local/bin/phpcs',
						standard: '~/Plugins/ruleset.xml',
						warningSeverity: 0
					}
				},
				warnings: {
					cwd: SOURCE_DIR,
					src: [
						'**/*.php',
						'!**/*.js',
						'!plugin-update-checker/**',
						'!node_modules/**'
					],
					options: {
						bin: '/usr/local/bin/phpcs',
						standard: '~/Plugins/ruleset.xml',
						warningSeverity: 1
					}
				}
			},
			clean: {
				options: {
					force: true
				},
				minified: [
					SOURCE_DIR + './include/*.min.js',
					SOURCE_DIR + './include/*.min.css',
					SOURCE_DIR + './tinymce/*.min.js'
				],
				zip: [
					SOURCE_DIR + 'subscribe2-for-cp.zip'
				]
			},
			prompt: {
				build: {
					options: {
						questions: [ {
							config: 'build',
							type: 'list',
							message: 'Prepare Major or Minor Release?',
							choices: [
								{ name: 'Major Release', value: 'release-major' },
								{ name: 'Minor Release', value: 'release-minor' },
								{ name: 'Patch Release', value: 'release-patch' },
								{ name: 'Quit', value: 'quit' }
							]
						} ],
						then: function( results ) {
							if ( 'quit' !== results.build ) {
								grunt.task.run( results.build );
							} else {
								grunt.log.ok( 'Quitting.' );
								return 0;
							}
						}
					}
				}
			},
			replace: {
				version: {
					options: {
						patterns: [ {
							match: /^define\(\s'[\w]*',\s'(\d+\.\d+[.]?[\d]*)'\s\);$/m,
							replacement: function() {
								var file    = grunt.file.read( SOURCE_DIR + 'subscribe2.php' );
								var regex   = /^[\w]*:\s(\d+\.\d+[.]?[\d]*)$/m;
								var matches = file.match( regex );
								return 'define( \'S2VERSION\', \'' + matches[1] + '\' );';
							}
						} ]
					},
					files: [ {
						expand: true,
						flatten: true,
						src: SOURCE_DIR + 'subscribe2.php',
						dest: SOURCE_DIR
					} ]
				}
			},
			terser: {
				options: {
					output: {
						ascii_only: true
					},
					ie8: true
				},
				core: {
					expand: true,
					cwd: SOURCE_DIR,
					dest: SOURCE_DIR,
					ext: '.min.js',
					src: [
						'gutenberg/*.js',
						'include/*.js',
						'tinymce/*.js',
						'!**/*.min.js'
					]
				}
			},
			cssmin: {
				options: {
					compatibility: 'ie7'
				},
				core: {
					expand: true,
					cwd: SOURCE_DIR,
					dest: SOURCE_DIR,
					ext: '.min.css',
					src: [
						'include/*.css'
					]
				}
			},
			imagemin: {
				core: {
					expand: true,
					cwd: SOURCE_DIR,
					src: [
						'include/*.{png,jpg,gif,jpeg}'
					],
					dest: SOURCE_DIR
				}
			},
			shell: {
				makepot: {
					cwd: SOURCE_DIR,
					command: "wp i18n make-pot . languages/subscribe2.pot --exclude=plugin-update-checker --headers='{\"Report-Msgid-Bugs-To\":\"https://github.com/mattyrob/subscribe2-for-cp/issues\"}'"
				}
			},
			addtextdomain: {
				s2cp: {
					options: {
						textdomain: 'subscribe2-for-cp',
						updateDomains: true
					},
					files: {
						src: [
							'*.php',
							'admin/*.php',
							'classes/*.php',
							'include/*.php'
						]
					}
				}
			},
			bump_wp_version: {
				dev: {
					options: {},
					files: {
						'subscribe2.php': 'subscribe2.php'
					}
				}
			},
			zip: {
				'release': {
					dest: 'subscribe2-for-cp.zip',
					router: function ( filepath ) {
						return 'subscribe2-for-cp/' + filepath;
					},
					src: [
						'subscribe2.php',
						'ChangeLog.txt',
						'license.txt',
						'ReadMe.txt',
						'admin/**',
						'classes/**',
						'include/**',
						'languages/**',
						'plugin-update-checker/**',
						'tinymce/**'
					]
				}
			}
		}
	);

	grunt.registerTask(
		'fixtest',
		[
			'shell:phpcspath'
		]
	);

	grunt.registerTask(
		'test',
		[
			'phpcs:warnings',
			'jshint:core',
			'eslint:core'
		]
	);

	grunt.registerTask(
		'testgrunt',
		[
			'jshint:grunt',
			'eslint:grunt'
		]
	);

	grunt.registerTask(
		'basictest',
		[
			'phpcs:errors',
			'jshint:core',
			'eslint:core'
		]
	);

	grunt.registerTask(
		'bump',
		[
			'bump_wp_version',
			'replace:version'
		]
	);

	grunt.registerTask(
		'build',
		[
			'clean:minified',
			'addtextdomain:s2cp',
			'terser',
			'cssmin',
			'imagemin',
			'shell:makepot'
		]
	);

	grunt.registerTask(
		'default',
		[
			'basictest'
		]
	);

	grunt.registerTask(
		'release',
		'Preparing new release...',
		function ( release ) {
			var releases = [ 'major', 'minor', 'patch' ];
			if ( 0 === arguments.length ) {
				grunt.log.writeln( 'Please specify release type, for example `grunt release:minor`' );
			} else {
				if ( releases.includes( release ) ) {
					grunt.task.run( 'release-' + release );
				} else {
					grunt.log.writeln( 'Please specify a valid release type' );
				}
			}
		}
	);

	grunt.registerTask(
		'release-major',
		'Preparing Major release...',
		function() {
			grunt.option( 'bump', 'major' );
			grunt.task.run( 'test', 'bump', 'build', 'zip' );
		}
	);

	grunt.registerTask(
		'release-minor',
		'Preparing Minor release...',
		function() {
			grunt.option( 'bump', 'minor' );
			grunt.task.run( 'test', 'bump', 'build', 'zip' );
		}
	);

	grunt.registerTask(
		'release-patch',
		'Preparing Patch release...',
		function() {
			grunt.option( 'bump', 'patch' );
			grunt.task.run( 'test', 'bump', 'build', 'zip' );
		}
	);
};
