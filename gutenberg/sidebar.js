// Version 1.0 - Initial version

( function( plugins, element, i18n, editPost, components, data, compose, apiFetch ) {
	var registerPlugin = plugins.registerPlugin,
		el = element.createElement,
		__ = i18n.__,
		Fragment = element.Fragment,
		PluginSidebar = editPost.PluginSidebar,
		PluginSidebarMoreMenuItem = editPost.PluginSidebarMoreMenuItem,
		PanelBody = components.PanelBody,
		PanelRow = components.PanelRow,
		CheckboxControl = components.CheckboxControl,
		Button = components.Button,
		select = data.select,
		dispatch = data.dispatch,
		withSelect = data.withSelect,
		withDispatch = data.withDispatch,
		Compose = compose.compose;

	var CheckboxControlMeta = Compose(
		withSelect( function( select, props ) {
			var s2mail = select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ props.fieldName ];
			return {
				metaChecked: ( 'no' === s2mail ? true : false )
			};
		}),
		withDispatch( function( dispatch, props ) {
			return {
				setMetaChecked: function( value ) {
					var s2mail = ( true === value ? 'no' : 'yes'  );
					dispatch( 'core/editor' ).editPost({ meta: { [props.fieldName]: s2mail } });
					dispatch( 'core/editor' ).savePost();
				}
			};
		})
	) ( function( props ) {
		return el(
			CheckboxControl,
			{
				label: __( 'Check here to disable sending of an email notification for this post/page', 'subscribe2' ),
				checked: props.metaChecked,
				onChange: function( content ) {
					props.setMetaChecked( content );
				}
			}
		);
	});

	var buttonClick = function() {
		var postid = select( 'core/editor' ).getCurrentPostId();
		apiFetch({ path: '/s2/v1/preview/' + postid });
		dispatch( 'core/notices' ).createInfoNotice( __( 'Attempt made to send email preview', 'subscribe2' ) );
	};

	var s2sidebar = function() {
		return el(
			Fragment,
			{},
			el(
				PluginSidebarMoreMenuItem,
				{
					target: 's2-sidebar',
					icon: 'email'
				},
				__( 'Subscribe2 Sidebar', 'subscribe2' )
			),
			el(
				PluginSidebar,
				{
					name: 's2-sidebar',
					title: __( 'Subscribe2 Sidebar', 'subscribe2' ),
					icon: 'email',
					isPinned: true,
					isPinnable: true,
					togglePin: true,
					togglesidebar: false
				},
				el(
					PanelBody,
					{
						title: __( 'Subscribe2 Override', 'subscribe2' ),
						initialOpen: true
					},
					el(
						PanelRow,
						{},
						el(
							CheckboxControlMeta,
							{
								fieldName: '_s2mail'
							}
						)
					)
				),
				el(
					PanelBody,
					{
						title: __( 'Subscribe2 Preview', 'subscribe2' ),
						initialOpen: true
					},
					el(
						PanelRow,
						{},
						el(
							'div',
							null,
							__( 'Send preview email of this post to currently logged in user:', 'subscribe2' )
						)
					),
					el(
						PanelRow,
						{},
						el(
							Button,
							{
								isDefault: true,
								onClick: buttonClick
							},
							__( 'Send Preview', 'subscribe2' )
						)
					)
				)
			)
		);
	};

	registerPlugin( 'subscribe2-sidebar', {
		render: s2sidebar
	});
} (
	wp.plugins,
	wp.element,
	wp.i18n,
	wp.editPost,
	wp.components,
	wp.data,
	wp.compose,
	wp.apiFetch
) );
