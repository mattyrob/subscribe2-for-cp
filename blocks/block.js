// Version 1.0 - Initial version
( function( blocks, i18n, element ) {
	var el = element.createElement,
		TextControl = blocks.InspectorControls.TextControl,
		CheckboxControl = blocks.InspectorControls.CheckboxControl,
		RadioControl = blocks.InspectorControls.RadioControl;

	blocks.registerBlockType( 'subscribe2-html/shortcode', {
		title: i18n.__( 'Subscribe2 HTML' ),
		icon: 'email',
		category: 'widgets',
		useOnce: true,
		supports: {
			customClassName: false,
			className: false
		},
		attributes: {
			hide: {
				type: 'string',
				value: 'none'
			},
			id: {
				type: 'string',
				value: ''
			},
			nojs: {
				type: 'boolean',
				value: false
			},
			antispam: {
				type: 'boolean',
				value: false
			},
			size: {
				type: 'number',
				value: '20'
			},
			wrap: {
				type: 'boolean',
				value: false
			}
		},
		edit: function( props ) {
			var hide = props.attributes.hide,
				id = props.attributes.id,
				nojs = props.attributes.nojs,
				antispam = props.attributes.antispam,
				size = props.attributes.size,
				wrap = props.attributes.wrap,
				focus = props.focus;

			function onChangeHide( newHide ) {
				props.setAttributes( { hide: newHide } );
			}
			function onChangeId( newId ) {
				props.setAttributes( { id: newId } );
			}
			function onChangeNojs() {
				var newNojs = ! nojs;
				props.setAttributes( { nojs: newNojs } );
			}
			function onChangeAntispam() {
				var newAntispam = ! antispam;
				props.setAttributes( { antispam: newAntispam } );
			}
			function onChangeSize( newSize ) {
				props.setAttributes( { size: newSize } );
			}
			function onChangeWrap() {
				var newWrap = ! wrap;
				props.setAttributes( { wrap: newWrap } );
			}

			return [
				!! focus && el(
					blocks.InspectorControls,
					{ key: 'subscribe2-html/inspector' },
					el( 'h3', {}, i18n.__( 'Subscribe2 Shortcode Parameters' ) ),
					el(
						RadioControl,
						{
							id: 'hide',
							label: i18n.__( 'Button Display Options' ),
							selected: hide || 'none',
							onChange: onChangeHide,
							options: [
								{ value: 'none', label: i18n.__( 'None' ) },
								{ value: 'subscribe', label: i18n.__( 'Subscribe' ) },
								{ value: 'unsubscribe', label: i18n.__( 'Unsubscribe' ) }
							]
						}
					),
					el(
						TextControl,
						{
							id: 'id',
							type: 'number',
							label: i18n.__( 'Page ID' ),
							value: id,
							onChange: onChangeId
						}
					),
					el(
						CheckboxControl,
						{
							id: 'nojs',
							label: i18n.__( 'Disable Javascript' ),
							checked: nojs,
							onChange: onChangeNojs
						}
					),
					el(
						CheckboxControl,
						{
							id: 'antispam',
							label: i18n.__( 'Disable Simple Anti-Spam Measures' ),
							checked: antispam,
							onChange: onChangeAntispam
						}
					),
					el(
						TextControl,
						{
							id: 'size',
							type: 'number',
							label: i18n.__( 'Textbox size' ),
							value: size,
							onChange: onChangeSize
						}
					),
					el(
						CheckboxControl,
						{
							id: 'wrap',
							label: i18n.__( 'Disable wrapping of form buttons' ),
							checked: wrap,
							onChange: onChangeWrap
						}
					)
				),
				el(
					'div', {
						key: 'subscribe2-html/block',
						style: { backgroundColor: '#ff0', color: '#000', padding: '2px', 'textAlign': 'center' }
					}, 'Subscribe2 HTML Shortcode'
				)
			];
		},
		save: function( props ) {
			var attributes = props.attributes;
			var hide = '', id = '', nojs = '', antispam = '', size = '', wrap = '';
			if ( 'subscribe' === attributes.hide ) {
				hide = ' hide=\'subscribe\'';
			} else if ( 'unsubscribe' === attributes.hide ) {
				hide = ' hide=\'unsubscribe\'';
			}
			if ( '' !== attributes.id ) {
				id = ' id=\'' + attributes.id + '\'';
			}
			if ( true === attributes.nojs ) {
				nojs = ' nojs=\'true\'';
			}
			if ( true === attributes.antispam ) {
				antispam = '  antispam=\'true\'';
			}
			if ( '' !== attributes.size && '20' !== attributes.size ) {
				size = ' size=\'' + attributes.size + '\'';
			}
			if ( true === attributes.wrap ) {
				wrap = ' wrap=\'false\'';
			}

			return '<p>[subscribe2' + hide + id + nojs + antispam + size + wrap + ']</p>';
		}
	} );
} ) (
	window.wp.blocks,
	window.wp.i18n,
	window.wp.element
);