<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Recovery\Install;

use Shopware\Recovery\Common\IOHelper;
use Shopware\Recovery\Install\Service\DatabaseService;
use Shopware\Recovery\Install\Struct\DatabaseConnectionInformation;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class DatabaseInteractor
{
    /**
     * @var IOHelper
     */
    private $IOHelper;

    public function __construct(IOHelper $IOHelper)
    {
        $this->IOHelper = $IOHelper;
    }

    /**
     * @return DatabaseConnectionInformation
     */
    public function askDatabaseConnectionInformation(
        DatabaseConnectionInformation $connectionInformation
    ) {
        $databaseHost = $this->askForDatabaseHostname($connectionInformation->hostname);
        $databasePort = $this->askForDatabasePort($connectionInformation->port);
        $question = new Question('Please enter database socket: ', $connectionInformation->socket);
        $databaseSocket = $this->askQuestion($question);
        $databaseUser = $this->askForDatabaseUsername($connectionInformation->username);
        $databasePassword = $this->askForDatabasePassword($connectionInformation->password);

        $connectionInformation = new DatabaseConnectionInformation([
            'hostname' => $databaseHost,
            'port' => $databasePort,
            'socket' => $databaseSocket,
            'username' => $databaseUser,
            'password' => $databasePassword,
        ]);

        return $connectionInformation;
    }

    /**
     * @return string
     */
    public function createDatabase(\PDO $connection)
    {
        $question = new Question('Please enter the name database to be created: ');
        $databaseName = $this->askQuestion($question);

        $service = new DatabaseService($connection);
        $service->createDatabase($databaseName);

        return $databaseName;
    }

    /**
     * @param string $databaseName
     *
     * @return bool
     */
    public function continueWithExistingTables($databaseName, \PDO $pdo)
    {
        $service = new DatabaseService($pdo);
        $tableCount = $service->getTableCount();
        if ($tableCount == 0) {
            return true;
        }

        $question = new ConfirmationQuestion(
            sprintf(
                'The database %s already contains %s tables. Continue? (yes/no) [no]',
                $databaseName,
                $tableCount
            ),
            false
        );

        return $this->askQuestion($question);
    }

    /**
     * Facade for asking questions
     *
     * @return string
     */
    public function askQuestion(Question $question)
    {
        return $this->IOHelper->ask($question);
    }

    /**
     * @param string $defaultHostname
     *
     * @return string
     */
    protected function askForDatabaseHostname(
        $defaultHostname
    ) {
        $question = new Question(sprintf('Please enter database host (%s): ', $defaultHostname), $defaultHostname);
        $question->setValidator(
            function ($answer) {
                if (trim($answer) === '') {
                    throw new \Exception('The database user can not be empty');
                }

                return $answer;
            }
        );

        $databaseHost = $this->askQuestion($question);

        return $databaseHost;
    }

    /**
     * @param string $defaultUsername
     *
     * @return string
     */
    protected function askForDatabaseUsername(
        $defaultUsername
    ) {
        if (empty($defaultUsername)) {
            $question = new Question('Please enter database user: ');
        } else {
            $question = new Question(sprintf('Please enter database user (%s): ', $defaultUsername), $defaultUsername);
        }

        $question->setValidator(
            function ($answer) {
                if (trim($answer) === '') {
                    throw new \Exception('The database user can not be empty');
                }

                return $answer;
            }
        );
        $databaseUser = $this->askQuestion($question);

        return $databaseUser;
    }

    /**
     * @param string $defaultPassword
     *
     * @return string
     */
    protected function askForDatabasePassword(
        $defaultPassword
    ) {
        if (empty($defaultPassword)) {
            $question = new Question('Please enter database password: ');
        } else {
            $question = new Question(sprintf('Please enter database password: (%s): ', $defaultPassword), $defaultPassword);
        }

        $databaseUser = $this->askQuestion($question);

        return $databaseUser;
    }

    /**
     * @param string $defaultPort
     *
     * @return string
     */
    private function askForDatabasePort(
        $defaultPort
    ) {
        $question = new Question(sprintf('Please enter database port (%s): ', $defaultPort), $defaultPort);
        $question->setValidator(
            function ($answer) {
                if (trim($answer) === '') {
                    throw new \Exception('The database port can not be empty');
                }

                if (!is_numeric($answer)) {
                    throw new \Exception('The database port must be a number');
                }

                return $answer;
            }
        );
        $databasePort = $this->askQuestion($question);

        return $databasePort;
    }
}
