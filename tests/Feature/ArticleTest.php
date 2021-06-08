<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Article;

class ArticleTest extends TestCase
{
    public function test_article_store_validation()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $this->json('POST', 'api/articles')
            ->assertStatus(422)
            ->assertJson([
                "message" => "The given data was invalid.",
                "errors" => [
                    "title" => ["The title field is required."],
                    "description" => ["The description field is required."],
                ]
            ]);
    }

    public function test_article_store()
    {
        // Arrange
        $user = User::factory()->create();
        $article = [
            'title' => 'Sample title',
            'description' => 'This is a description'
        ];

        // Act
        $this->actingAs($user, 'api');
        $response = $this->json('POST', 'api/articles', $article);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('articles', $article);
    }

    public function test_list_of_article()
    {
        $user = User::factory()->create();
        $numberOfRecords = 7;
        $perPage = 10;

        Article::factory()->count($numberOfRecords)->create();

        $this->actingAs($user, 'api');
        $response = $this->json('GET', 'api/articles?page=1&perPage='.$perPage);

        $response->assertStatus(200);

        if ($numberOfRecords < $perPage) {
            $perPage = $numberOfRecords;
        }
        $responseArray = json_decode($response->getContent());
        $this->assertEquals(count($responseArray->data), $perPage);
    }

    public function test_retrieve_article()
    {
        $user = User::factory()->create();

        $article = Article::factory()->create();

        $this->actingAs($user, 'api');
        $response = $this->json('GET', 'api/articles/'.$article->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'created_at',
                'updated_at',
            ])
            ->assertExactJson($article->toArray());
    }

    public function test_update_article()
    {
        $user = User::factory()->create();

        $article = Article::factory()->create([
            'title' => 'New Article',
            'description' => 'New description',
        ]);

        $this->actingAs($user, 'api');

        $payload = [
            'title' => 'Updated title',
            'description' => 'Updated description',
        ];

        $response = $this->json('PUT', 'api/articles/'.$article->id, $payload);
        $response->assertStatus(200)
            ->assertJson($payload);
        
        $this->assertDatabaseHas('articles', $payload);
    }
}
