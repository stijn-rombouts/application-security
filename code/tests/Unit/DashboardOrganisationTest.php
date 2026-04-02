<?php

namespace Tests\Unit;

use App\Livewire\DashboardOrganisationOverview;
use Tests\TestCase;

class DashboardOrganisationTest extends TestCase
{
    public function test_component_variables_exist_in_view()
    {
        // Expected variables passed to compact()
        $expectedVariables = [
            'totalUsers',
            'totalTests',
            'interestFields',
            'mostChosenIntrestFields',
            'countAttempts',
            'completionScore',
        ];

        //Create component
        $component = new DashboardOrganisationOverview();

        //get information of component
        $reflection = new \ReflectionMethod($component, 'render');
        $body = file($reflection->getFileName());
        $lines = array_slice($body, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1);
        $renderCode = implode("", $lines);

        //check if variables are in component
        foreach ($expectedVariables as $var) {
            $this->assertStringContainsString("'$var'", $renderCode, "Variable '$var' is missing in compact()");
        }
    }
}
