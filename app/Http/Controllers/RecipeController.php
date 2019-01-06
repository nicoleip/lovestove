<?php 

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Recipe;
// use App\Recipe;

class RecipeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getRecipe(Request $request) {
        $recipeId = $request->id;
        $client = new Client();
        $result = $client->request('POST', 'http://lovestove.com/api/get', [
            'json' => [    
                'recipeId' => $recipeId,
            ]
        ]);        

        $result = json_decode($result->getBody());
       
        $data = $this->renderRecipe($result);      

        return $data;
    }

   
    public function renderRecipe($recipe)
    {       
        $newIngr = $this->parseIngredients($recipe->ingredients);
        
        $recipe->ingredients = $newIngr;
        $recipe->people = $this->calculateServings();
        $recipe->time = $this->calculateTime($recipe);
        $markupStart = '
        <figure class="recipe__fig">        
                <img src="'.$recipe->image_url.'" id="recipe-img" alt="Tomato" class="recipe__img">
                <h1 class="recipe__title">
                    <span id="recipe-title">'.$recipe->title.'</span>
                </h1>
            </figure>
            <div class="recipe__details">
                <div class="recipe__info">
                    <svg class="recipe__info-icon">
                        <use href="img/icons.svg#icon-stopwatch"></use>
                    </svg>
                    <span class="recipe__info-data recipe__info-data--minutes" id="recipe-time">'.$recipe->time.'</span>
                    <span class="recipe__info-text"> minutes</span>
                </div>
                <div class="recipe__info">
                    <svg class="recipe__info-icon">
                        <use href="img/icons.svg#icon-man"></use>
                    </svg>
                    <span class="recipe__info-data recipe__info-data--people" id="recipe-servings">'.$recipe->people.'</span>
                    <span class="recipe__info-text"> servings</span>

                    <div class="recipe__info-buttons">
                        <button class="btn-tiny" data-value="dec">
                            <svg>
                                <use href="img/icons.svg#icon-circle-with-minus"></use>
                            </svg>
                        </button>
                        <button class="btn-tiny" data-value="inc">
                            <svg>
                                <use href="img/icons.svg#icon-circle-with-plus"></use>
                            </svg>
                        </button>
                    </div>

                </div>
                <button class="recipe__love">
                    <svg class="header__likes">
                        <use href="img/icons.svg#icon-heart-outlined"></use>
                    </svg>
                </button>
            </div>
            <div class="recipe__ingredients">
                <ul class="recipe__ingredient-list">';
            
            $ingredient = '';    
            foreach ($recipe->ingredients as $key=>$value)
            {
                $ingredient .= '<li class="recipe__item">
                <svg class="recipe__icon">
                    <use href="img/icons.svg#icon-check"></use>
                </svg>
                <div class="recipe__count">'.$recipe->ingredients[$key]['count'].'</div>
                <div class="recipe__ingredient">
                    <span class="recipe__unit">'.$recipe->ingredients[$key]['unit'].'</span>
                    '.$recipe->ingredients[$key]['ingredient'].'
                </div>
            </li>';
            }
            
            $markupEnd = '
            </ul>

            <button class="btn-small recipe__btn">
                <svg class="search__icon">
                    <use href="img/icons.svg#icon-shopping-cart"></use>
                </svg>
                <span>Add to shopping list</span>
            </button>
        </div>
            <div class="recipe__directions">
                <h2 class="heading-2">How to cook it</h2>
                <p class="recipe__directions-text">
                    This recipe was carefully designed and tested by
                    <span class="recipe__by" id="recipe-publisher">'.$recipe->publisher.'</span>. Please check out directions at their website.
                </p>
                <a class="btn-small recipe__btn" href="'.$recipe->source_url.'" id="recipe-source-url" target="_blank">
                    <span>Directions</span>
                    <svg class="search__icon">
                        <use href="img/icons.svg#icon-triangle-right"></use>
                    </svg>

                </a>
            </div>
        ';

        $markup = $markupStart.$ingredient.$markupEnd;

        
        return $markup;
      
    }

    
    public function calculateTime($recipe)
    {
        $ingredientsCount = count($recipe->ingredients);
        $periods = ceil($ingredientsCount / 3);
        $time = $periods * 15;

        return $time;
    }

    public function calculateServings()
    {
        $servings = 4;
        return $servings;
    }

    public function mapIngredients($ingredient) {
        $unitsLong = ['tablespoons', 'tablespoon', 'ounces', 'ounce', 'teaspoons', 'teaspoon', 'cups', 'pounds'];
        $unitsShort = ['tbsp', 'tbsp', 'oz', 'oz', 'tsp', 'tsp', 'cup', 'pound' ];

        $ingredient = strtolower($ingredient);

        
        foreach($unitsLong as $key=>$value){
            $ingredient = str_replace($value, $unitsShort[$key], $ingredient);           
        }        

        $ingredient = preg_replace('/ *\([^)]*\) * /', ' ', $ingredient);        

        $arrIngredient = explode(' ', $ingredient);

        
        foreach($arrIngredient as $key=>$value){            
            
            // There is a unit
            if(count(array_intersect($arrIngredient, $unitsShort))) {
                $unitIndex = key(array_intersect($arrIngredient, $unitsShort));                          
                $arrCount = array_slice($arrIngredient, 0, $unitIndex);
                if(count($arrCount) === 1) {
                    //if the count is smth like '1-1/3', replace the - with + and then evaluate
                    if(strpos($arrIngredient[0], '-')){
                        $strr = str_replace('-', '+', $arrIngredient[0]);
                        $countt = eval("return ($strr);");
                        $ingredientObj['count'] = $countt;
                    } else {
                    $ingredientObj['count'] = $arrIngredient[0];
                    }
                } else {
                    $str = implode("+", array_slice($arrIngredient, 0, $unitIndex));
                    $count = eval("return ($str);");
                    $ingredientObj['count'] = $count;
                }
                $ingredientObj['unit'] = $arrIngredient[$unitIndex];
                $ingredientObj['ingredient'] = implode(" ",(array_slice($arrIngredient, $unitIndex+1)));
                return $ingredientObj;
                // There is no unit...
            } else {
                // ..but the first element is number
                if (is_numeric($arrIngredient[0])){
                    $ingredientObj['count'] = $arrIngredient[0];
                    $ingredientObj['unit'] = '';
                    $ingredientObj['ingredient'] = implode(" ",(array_slice($arrIngredient, 1)));
                    return $ingredientObj;
                   // ... and no number
                } else {
                    $ingredientObj['count'] = "1";
                    $ingredientObj['unit'] = '';
                    $ingredientObj['ingredient'] = $ingredient;
                    return $ingredientObj;
                }
            } 
        }      
                
    }        

    public function parseIngredients($ingredients)
    {
        $newIngredients = array_map([$this, 'mapIngredients'], $ingredients);

        return $newIngredients;
    }

    public function save(Request $request) {
        
        $recipe = new Recipe();
        $recipe->title = $request->recipe_title;
        $recipe->image_url = $request->recipe_img_url;
        $recipe->time = $request->recipe_time;
        $recipe->people = $request->recipe_servings;
        $recipe->ingredients = json_encode($request->recipe_ingredients);
        $recipe->publisher = $request->recipe_publisher;
        $recipe->source_url = $request->recipe_source_url;
        $recipe->recipe_id = $request->recipe_id;

        $recipe->save();

        $user = Auth::user();

        $user->recipes()->attach($recipe);
    }

    public function getSavedRecipes(Request $request)
    {
        $markup = '';
        $user = Auth::user();
        $recipes = json_decode($user->recipes);

        if(!$recipes)
        {
            $markup = 'No saved recipes yet!';
        } else {
            foreach($recipes as $recipe) {

        $markup .='<li>
                <a class="likes__link" href=#"'.$recipe->recipe_id.'">
                    <figure class="likes__fig">
                        <img src="'.$recipe->image_url.'"alt="Test">
                    </figure>
                    <div class="likes__data">
                        <h4 class="likes__name">'.$recipe->title.'</h4>
                        <p class="likes__author">'.$recipe->publisher.'</p>
                    </div>
                </a>
        </li>';       
            }

            return $markup;
        }
        
        return $markup;
    }
}