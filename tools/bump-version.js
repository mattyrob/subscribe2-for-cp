const fs = require( 'fs' );
const path = require( 'path' );

const pluginFile = path.join( __dirname, '..', 'subscribe2.php' );
console.log( pluginFile );
// which part to bump: patch ( default ), minor, major
const bumpType = process.argv[ 2 ] || 'patch';

function bumpSemver( version, type = 'patch' ) {
	const parts = version.split( '.' ).map( Number );
	let [ major, minor, patch ] = parts;

	if ( type === 'major' ) {
		major += 1;
		minor = 0;
		patch = 0;
	} else if ( type === 'minor' ) {
		minor += 1;
		patch = 0;
	} else {
		patch += 1;
	}

	return [ major, minor, patch ].join( '.' );
}

function bumpPluginFile() {
	let contents = fs.readFileSync( pluginFile, 'utf8' );

	// Match version in header: "Version: 1.2.3"
	const headerRegex = /(Version:\s*)(\d+\.\d+\.\d+)/;
	const match = contents.match( headerRegex );

	if ( ! match ) {
		throw new Error( 'Could not find Version header in plugin file' );
	}

	const oldVersion = match[ 2 ];
	const newVersion = bumpSemver( oldVersion, bumpType );

	// Replace all occurrences of oldVersion with newVersion
	const versionRegex = new RegExp( oldVersion.replace( /\./g, '\\.' ), 'g' );
	contents = contents.replace( versionRegex, newVersion );

	fs.writeFileSync( pluginFile, contents, 'utf8' );
	return { oldVersion, newVersion };
}

function main() {
	const { oldVersion, newVersion } = bumpPluginFile();
	console.log( `Bumped version: ${oldVersion} -> ${newVersion}` );
}

main();
