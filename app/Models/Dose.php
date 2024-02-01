<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Dose find(mixed $DOSE_ID)
 */
class Dose extends Model {
    use HasFactory;

    protected $table = "DOSE";
    protected $primaryKey = "DOSE_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "DOSE_NOME",
    ];
    protected $casts = [

    ];

    public static function search($doseNome = null) {
        return self::with([])
            ->when($doseNome, function ($q) use ($doseNome) {
                $q->where("DOSE_NOME", 'like', "%$doseNome%");
            })
            ->paginate(10);
    }
}
