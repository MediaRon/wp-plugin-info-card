import classnames from 'classnames';

const { __ } = wp.i18n;
const { Fragment } = wp.element;

const PluginCard = ( props ) => {
	const wrapperClasses = classnames( `align${ props.align }`, {
		'wp-pic-wrapper': true,
		'wp-pic-card': true,
		'wp-pic-not-found': true,
	} );
	const classes = classnames( props.scheme, {
		'wp-pic': true,
		'wp-pic-card': true,
		plugin: true,
		large: true,
	} );

	return (
		<div className={ wrapperClasses }>
			<div className={ classes }>
				<div className="wp-pic-flip" style={ { display: 'none' } }>
					<div className="wp-pic-face wp-pic-front">
						<h2>
							{ __( 'Item not found.', 'wp-plugin-info-card' ) }
						</h2>
					</div>
				</div>
			</div>
		</div>
	);
};

export default PluginCard;
