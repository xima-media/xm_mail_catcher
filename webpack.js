const path = require('path');

module.exports = {
	mode: 'development',
	devtool: 'inline-source-map',
	watch: true,
	entry: {
		BackendModule: './Resources/Private/TypeScript/BackendModule.ts'
	},
	output: {
		filename: 'Resources/Public/JavaScript/[name].js',
		path: path.resolve(__dirname, '.'),
		libraryTarget: 'amd',
		library: "TYPO3/CMS/XmMailCatcher/[name]"
	},
	module: {
		rules: [
			{
				test: /\.tsx?$/,
				use: 'ts-loader'
			}
		],
	},
	resolve: {
		extensions: ['.tsx', '.ts', '.js'],
		alias: {
			'TYPO3/CMS/Backend': path.resolve(__dirname, 'typo3_src/TypeScript/backend/Resources/Public/TypeScript'),
			'TYPO3/CMS/Core': path.resolve(__dirname, 'typo3_src/TypeScript/core/Resources/Public/TypeScript'),
			'TYPO3/CMS/XmMailCatcher': path.resolve(__dirname, 'Resources/Private/TypeScript')
		}
	},
	externals: {
		'TYPO3/CMS/Backend/Modal': 'TYPO3/CMS/Backend/Modal',
		'TYPO3/CMS/Backend/Icons': 'TYPO3/CMS/Backend/Icons',
		'TYPO3/CMS/Backend/Notification': 'TYPO3/CMS/Backend/Notification',
		'jquery': 'jquery'
	}
};
