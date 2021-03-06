( function (blocks, editor, components, i18n, element ) {

	const { __ } = wp.i18n;
	var el = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var BlockControls = wp.blocks.BlockControls;
	var InspectorControls = wp.blocks.InspectorControls;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var PanelBody = components.PanelBody;

	registerBlockType( 'doliconnect/data-request-form', {
		title: __( 'Privacy Data Request Form', 'doliconnect'),
		icon: 'id-alt',
		category: 'widgets',
		keywords: [ __( 'form', 'doliconnect'), __( 'data', 'doliconnect'), __( 'request', 'doliconnect') ],
		attributes: {
			request_type: {
				type: 'string'
			},
		},
		edit: function( props ) {

			var attributes = props.attributes;
			var request_type = props.attributes.request_type;

			return [
				el(
					'div', {
						className: 'data-request-form-wrapper',
						style: {
							fontStyle: 'italic',
							color: '#333333',
							backgroundColor: '#eaeaea',
							paddingTop: '1em',
							paddingBottom: '1.5em',
							marginBottom: '0'
						}
					},
					el(
						'p', { 
							className: 'data-request-form-label',
							style: {
								textAlign: 'center',
								fontSize: '2em'
							}
						},
						__( 'Privacy Data request form', 'doliconnect')
					),
					el(
						'p', { 
							className: 'data-request-form-label',
							style: {
								paddingLeft: '2em',
								paddingRight: '2em'
							}
						},
						__( 'This block displays a Privacy Data Request Form.', 'doliconnect'),
					),
					el(
						'p', { 
							className: 'data-request-form-label',
							style: {
								paddingLeft: '2em',
								paddingRight: '2em'
							}
						},
						__( 'By default, the form shows both export and remove Data Request options (it’s up to the visitor). You can set it either to "both", "export" or "remove".', 'doliconnect'),
					),
					el(
						'div', { 
							className: 'data-request-form-p',
							style: {
								paddingLeft: '2em',
								paddingRight: '2em'
							}
						},
						el(
							'label', { 
								'for': 'data-request-form-select',
								style: {
									display: 'block'
								}
							},
							__( 'Request type:', 'doliconnect' ),
						),
						el(
							SelectControl, { 
								className: 'data-request-form-select',
								'name': 'data-request-form-select',
								options: [
									{ label: __( 'Both Export and Remove', 'doliconnect' ), value: 'both' },
									{ label: __( 'Data Export form only', 'doliconnect' ), value: 'export' },
									{ label: __( 'Data Remove form only', 'doliconnect' ), value: 'remove' },
								],
								onChange: ( value ) => {
									props.setAttributes( { request_type: value } );
								},
								value: props.attributes.request_type
							},
						)
					)
				),
			]
		},
		save: function() {
			return null;
		}
	} );
}(
	window.wp.blocks,
	window.wp.editor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element
) );