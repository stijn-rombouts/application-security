<?php

namespace Tests\Unit;

use App\Models\Faq;
use App\View\Components\Faq as FaqComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_with_faqs_from_database()
    {
        // Arrange: Create test FAQs
        $faq1 = Faq::factory()->create([
            'question' => 'What is Laravel?',
            'answer' => 'Laravel is a PHP framework.'
        ]);
        
        $faq2 = Faq::factory()->create([
            'question' => 'How to install Laravel?',
            'answer' => 'Use composer create-project.'
        ]);

        // Act: Create component instance
        $component = new FaqComponent();

        // Assert: Component has the correct FAQs
        $this->assertCount(2, $component->faqs);
        $this->assertEquals('What is Laravel?', $component->faqs[0]->question);
        $this->assertEquals('How to install Laravel?', $component->faqs[1]->question);
    }

    public function test_component_accepts_custom_faqs()
    {
        // Arrange: Create custom FAQ collection
        $customFaqs = collect([
            (object)['question' => 'Custom Q1', 'answer' => 'Custom A1'],
            (object)['question' => 'Custom Q2', 'answer' => 'Custom A2'],
        ]);

        // Act: Create component with custom FAQs
        $component = new FaqComponent($customFaqs);

        // Assert: Component uses custom FAQs
        $this->assertCount(2, $component->faqs);
        $this->assertEquals('Custom Q1', $component->faqs[0]->question);
    }

    public function test_component_renders_view()
    {
        // Arrange: Create test FAQ
        Faq::factory()->create([
            'question' => 'Test Question',
            'answer' => 'Test Answer'
        ]);

        // Act: Render the component
        $component = new FaqComponent();
        $view = $component->render();

        // Assert: View is correct
        $this->assertEquals('components.faq', $view->name());
    }

    public function test_component_view_contains_faq_data()
    {
        // Arrange: Create test FAQ
        Faq::factory()->create([
            'question' => 'What is testing?',
            'answer' => 'Testing ensures code quality.'
        ]);

        // Act: Render component view
        $component = new FaqComponent();
        $rendered = $component->render()->with($component->data())->render();

        // Assert: Rendered HTML contains FAQ data
        $this->assertStringContainsString('What is testing?', $rendered);
        $this->assertStringContainsString('Testing ensures code quality.', $rendered);
    }

    public function test_component_handles_empty_faqs()
    {
        // Arrange: No FAQs in database
        
        // Act: Create component
        $component = new FaqComponent();

        // Assert: Component has empty collection
        $this->assertCount(0, $component->faqs);
    }

    public function test_component_with_multiple_faqs_renders_all()
    {
        // Arrange: Create multiple FAQs
        $faqs = Faq::factory()->count(5)->create();

        // Act: Create component
        $component = new FaqComponent();

        // Assert: All FAQs are included
        $this->assertCount(5, $component->faqs);
    }

    public function test_component_with_no_faqs_renders_no_faq_items()
    {
        // Arrange: No FAQs in database
        
        // Act: Create component and render
        $component = new FaqComponent();
        $rendered = $component->render()->with($component->data())->render();
        
        // Assert: No FAQ items are rendered (no buttons or answers)
        $this->assertStringNotContainsString('<button', $rendered);
        $this->assertStringNotContainsString('x-data="{ open: false }"', $rendered);
        
        // Assert: Only the container and heading are present
        $this->assertStringNotContainsString('FAQ', $rendered);
        $this->assertStringNotContainsString('space-y-4', $rendered);
    }

    public function test_component_renders_accordion_structure()
    {
        // Arrange: Create test FAQ
        Faq::factory()->create([
            'question' => 'Accordion Test',
            'answer' => 'Accordion Answer'
        ]);

        // Act: Render component
        $component = new FaqComponent();
        $rendered = $component->render()->with($component->data())->render();

        // Assert: Accordion structure is present
        $this->assertStringContainsString('x-data="{ open: false }"', $rendered);
        $this->assertStringContainsString('@click="open = !open"', $rendered);
        $this->assertStringContainsString('x-show="open"', $rendered);
        $this->assertStringContainsString('<button', $rendered);
    }

    public function test_component_renders_chevron_icon()
    {
        // Arrange: Create test FAQ
        Faq::factory()->create([
            'question' => 'Icon Test',
            'answer' => 'Icon Answer'
        ]);

        // Act: Render component
        $component = new FaqComponent();
        $rendered = $component->render()->with($component->data())->render();

        // Assert: SVG chevron icon is present
        $this->assertStringContainsString('<svg', $rendered);
        $this->assertStringContainsString(':class="{ \'rotate-180\': open }"', $rendered);
        $this->assertStringContainsString('viewBox="0 0 24 24"', $rendered);
    }

    public function test_component_renders_with_proper_styling()
    {
        // Arrange: Create test FAQ
        Faq::factory()->create([
            'question' => 'Style Test',
            'answer' => 'Style Answer'
        ]);

        // Act: Render component
        $component = new FaqComponent();
        $rendered = $component->render()->with($component->data())->render();

        // Assert: Key styling classes are present
        $this->assertStringContainsString('border-gray-200 dark:border-gray-700', $rendered);
        $this->assertStringContainsString('rounded-lg', $rendered);
        $this->assertStringContainsString('bg-gray-50 dark:bg-gray-800', $rendered);
    }
}