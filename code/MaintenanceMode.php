<?php
/**
 * Ability to easily toggle maintenance mode via CLI. To run this command:
 *
 * 		sake dev/tasks/MaintenanceMode [on|off]
 *
 *
 * @package maintenancemode
 *
 * @author Patrick Nelson <pat@catchyour.com>
 *
 * @since 2015-10-08
 */

class MaintenanceMode extends BuildTask
{
	protected $title = 'Maintance Mode Task';
	protected $description = 'Ability to easily toggle maintenance mode via CLI.';
	protected $enabled = true;

	/**
	 * @param	SS_HTTPRequest $request
	 */
	public function run($request)
	{
		// Only allow execution from the command line (for simplicity).
		if (!Director::is_cli()) {
			echo '<p>Sorry, but this can only be run from the command line.</p>';
			return;
		}

		try {
			// Get and validate desired maintenance mode setting.
			$get = $request->getVars();
			if (empty($get['args'])) {
				throw new Exception("Please provide an argument (e.g. 'on' or 'off').", 1);
			}

			$arg = strtolower(current($get['args']));
			if ($arg != 'on' && $arg != 'off') {
				throw new Exception("Invalid argument: '$arg' (expected 'on' or 'off')", 2);
			}

			// Get and write site configuration now.
			$config = SiteConfig::current_site_config();
			$previous = (!empty($config->MaintenanceMode) ? 'on' : 'off');
			$config->MaintenanceMode = ($arg == 'on');
			$config->write();

			// Output status and exit.
			if ($arg != $previous) {
				$this->output("Maintenance mode is now '$arg'.");
			} else {
				$this->output("NOTE: Maintenance mode was already '$arg' (nothing has changed).");
			}

		} catch (Exception $e) {
			$this->output('ERROR: '.$e->getMessage());
			if ($e->getCode() <= 2) {
				$this->output('Usage: sake dev/tasks/MaintenanceMode [on|off]');
			}

		}
	}

	####################
	## HELPER METHODS ##
	####################

	/**
	 * Output helper.
	 *
	 * @param $text
	 */
	protected function output($text)
	{
		echo "$text\n";
	}
}
