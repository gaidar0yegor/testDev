<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\MinkExtension\Context\MinkContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Fidry\AliceDataFixtures\LoaderInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
final class RdiContext extends MinkContext
{
    /** @var Response|null */
    private $response;

    private $doctrine;

    private $loader;

    private $fixturesBasePath;

    public function __construct(
        ManagerRegistry $doctrine,
        LoaderInterface $loader,
        string $fixturesBasePath
    ) {
        $this->doctrine = $doctrine;
        $this->loader = $loader;
        $this->fixturesBasePath = $fixturesBasePath;
    }

    /**
     * @Given I have loaded fixtures from :filename
     */
    public function iLoadedFixturesFrom($filename)
    {
        $managers = $this->doctrine->getManagers();

        foreach ($managers as $manager) {
            if ($manager instanceof EntityManagerInterface) {
                $schemaTool = new SchemaTool($manager);
                $schemaTool->dropDatabase();
                $schemaTool->createSchema($manager->getMetadataFactory()->getAllMetadata());
            }
        }

        $this->loader->load([$this->fixturesBasePath.$filename]);
    }

    /**
     * Vérifie que la ligne du table qui contient un texte contient aussi un autre texte.
     * Exemple:
     *      Then I should see "Contributeur" in the "Projet 3" row
     *
     * @Then I should see :text in the :rowText row
     */
    public function iShouldSeeTextInTheRow($text, $rowText)
    {
        $this->iShouldSeeTextInTheElementContainingText($text, 'table tr', $rowText);
    }

    /**
     * Vérifie que la ligne du table qui contient un texte contient aussi un autre texte.
     * Exemple:
     *      Then I should see "Contributeur" in the "Projet 3" row
     *
     * @Then I should see :text in the :element element containing :textContainer
     * @throws \Exception
     */
    public function iShouldSeeTextInTheElementContainingText($text, $element, $textContainer)
    {
        $rowSelector = sprintf('%s:contains("%s")', $element, $textContainer);
        $row = $this->getSession()->getPage()->find('css', $rowSelector);

        if (!$row) {
            throw new \Exception(sprintf('Cannot find any "%s" element containing the text "%s"', $element, $textContainer));
        }

        $this->assertElementContainsText($rowSelector, $text);
    }

    /**
     * Exemple:
     *      Then I should find toastr message "Le projet a été créé avec succès"
     *
     * @When /^(?:|I )should find toastr message "(?P<message>(?:[^"]|\\")*)"$/
     */
    public function iShouldFindToastrMessage($message)
    {
        $scriptElement = $this->getSession()->getPage()->find('css', 'html script._flash_toastr_messages');
        $scriptContent = html_entity_decode($scriptElement->getText());

        return strpos($scriptContent, $message) !== false;
    }
}
