import classnames from 'classnames';
import Logo from '../Logo';

const { __ } = wp.i18n;
const { Fragment } = wp.element;

const PluginCard = ( props ) => {
	const wrapperClasses = classnames( `align${ props.align }`, {
		large: true,
		'wp-pic-wrapper': true,
		'wp-pic-card': true,
		'wp-pic-not-found': true,
	} );
	const classes = classnames( props.scheme, {
		'wp-pic-card': true,
		large: true,
	} );

	return (
		<div className={ wrapperClasses }>
			<div className={ classes }>
				<div className="wp-pic-large" style={ { display: 'none' } }>
					<div className="wp-pic-large-content">
						<Fragment>
							<span className="wp-pic-pulse">
								<Logo />
							</span>
							<h2>
								{ __(
									'Item slug not found.',
									'wp-plugin-info-card'
								) }
							</h2>
							<ul>
								<li>
									<strong>
										{ __( 'Type', 'wp-plugin-info-card' ) }:{ ' ' }
									</strong>
									<span className="wp-pic-not-found-type">
										{ props.type }
									</span>
								</li>
								<li>
									<strong>
										{ __( 'Slug', 'wp-plugin-info-card' ) }:{ ' ' }
									</strong>
									<span className="wp-pic-not-found-slug">
										{ props.slug }
									</span>
								</li>
							</ul>
						</Fragment>
					</div>
				</div>
			</div>
		</div>
	);
};

PluginCard.defaultProps = {
	type: 'plugin',
	slug: '',
	align: 'full',
};

export default PluginCard;
