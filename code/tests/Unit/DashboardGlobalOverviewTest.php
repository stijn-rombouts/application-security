<?php

namespace Tests\Unit;

use App\Livewire\DashboardGlobalOverview;
use Tests\TestCase;

class DashboardGlobalOverviewTest extends TestCase
{
    public function test_component_variables_exist_in_view()
    {
        // Expected variables
        $expectedVariables = [
            'totalUsers',
            'totalTests',
            'interestFields',
            'mostChosenIntrestFields',
            'totalOrganisations',
            'completionScore',
        ];

        //Make new object of component and give all lines that are in the component
        $component = new DashboardGlobalOverview();
        $reflection = new \ReflectionMethod($component, 'render');
        $body = file($reflection->getFileName());
        $lines = array_slice($body, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1);
        $renderCode = implode("", $lines);

        // Check if every variable is in the compact()
        foreach ($expectedVariables as $var) {
            $this->assertStringContainsString("'$var'", $renderCode, "Variable '$var' is missing in compact()");
        }
    }
}
