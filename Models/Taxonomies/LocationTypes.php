<?

namespace Syltaen\Models\Taxonomies;

class LocationTypes extends Taxonomy
{

    const SLUG = "location-types";
    const NAME = "Location types";
    const DESC = "Categories for the different locations.";

    public function __construct()
    {
        $this->termsFields = [
            "icon",
            "pin"
        ];
    }
}