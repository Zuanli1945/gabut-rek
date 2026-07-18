<?php

namespace Database\Seeders;

use App\Enums\MaterialType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $aromachemicals = [
            'Hedione', 'Hedione HC', 'Iso E Super', 'Iso Gamma Super', 'Verdox',
            'Romandolide', 'Galaxolide', 'Habanolide', 'Cashmeran', 'Vertofix',
            'Cedramber', 'Ambermore', 'Ambroxan', 'Ambrox Super', 'Super',
            'Dihydromyrcenol', 'Linalool', 'Linalyl Acetate', 'Limonene',
            'Geraniol', 'Citral', 'Nerolidol', 'Benzyl Acetate', 'Hexyl Cinnamal',
            'Methyl Ionone', 'Alpha-Isomethyl Ionone', 'Ionone Beta',
            'Orris Concrete', 'Orris Butter', 'Coumarin', 'Vanillin',
            'Ethyl Vanillin', 'Heliotropin', 'Maltol', 'Ethyl Maltol',
            'Calone', 'Norlimbanol', 'Cedarwood Virginia', 'Sandalore',
            'Javanol', 'Timbersilk', 'Clearwood', 'Patchouli Heart', 'Keora',
        ];

        $essentialOils = [
            'Lavender', 'Bergamot', 'Lemon', 'Sweet Orange', 'Grapefruit',
            'Ylang Ylang', 'Geranium', 'Rosemary', 'Peppermint', 'Eucalyptus',
            'Tea Tree', 'Clary Sage', 'Cedarwood Atlas', 'Cedarwood Himalayan',
            'Sandalwood Mysore', 'Sandalwood Australian', 'Vetiver Haiti',
            'Patchouli', 'Frankincense', 'Myrrh', 'Petitgrain', 'Neroli',
            'Lemon Verbena', 'Lemongrass', 'Basil', 'Juniper Berry', 'Cypress',
            'Cinnamon Bark', 'Clove Bud', 'Cardamom', 'Ginger', 'Black Pepper',
            'Litsea Cubeba',
        ];

        $absolutes = [
            'Rose Absolute', 'Jasmine Absolute (Sambac)', 'Jasmine Absolute (Grandiflorum)',
            'Tuberose Absolute', 'Orange Flower Absolute (Neroli Abs.)',
            'Ylang Ylang Absolute', 'Mimosa Absolute', 'Orris Absolute',
            'Violet Leaf Absolute', 'Benzoin Absolute', 'Labdanum Absolute',
            'Opoponax Absolute', 'Peru Balsam Absolute', 'Tolu Balsam Absolute',
            'Mastic Absolute', 'Frankincense Absolute', 'Sandalwood Absolute',
            'Vetiver Absolute', 'Patchouli Absolute', 'Nagarmotha Absolute',
            'Galbanum Absolute', 'Cassie Absolute', 'Acacia Absolute',
        ];

        $accords = [
            'Floral Accord', 'Oriental Accord', 'Woody Accord',
            'Fresh/Aquatic Accord', 'Citrus Accord', 'Gourmand Accord',
            'Fougère Accord', 'Chypre Accord', 'Amber Accord', 'Musk Accord',
            'Leather Accord', 'Tobacco Accord', 'Earthy/Rooty Accord',
            'Green Accord', 'Powdery Accord',
        ];

        $inserts = [];

        foreach ($aromachemicals as $name) {
            $inserts[] = ['type' => 'aromachemical', 'name' => $name, 'is_custom' => false, 'created_at' => $now, 'updated_at' => $now];
        }
        foreach ($essentialOils as $name) {
            $inserts[] = ['type' => 'essential_oil', 'name' => $name, 'is_custom' => false, 'created_at' => $now, 'updated_at' => $now];
        }
        foreach ($absolutes as $name) {
            $inserts[] = ['type' => 'absolute', 'name' => $name, 'is_custom' => false, 'created_at' => $now, 'updated_at' => $now];
        }
        foreach ($accords as $name) {
            $inserts[] = ['type' => 'accord', 'name' => $name, 'is_custom' => false, 'created_at' => $now, 'updated_at' => $now];
        }

        DB::table('sub_categories')->insert($inserts);
    }
}
