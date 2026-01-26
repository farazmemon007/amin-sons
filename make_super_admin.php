<?php
require 'bootstrap/app.php';
use App\Models\User;
use Spatie\Permission\Models\Role;

// یہاں اپنی user ID ڈالیں (عام طور پر 1)
$userId = 1;

$user = User::find($userId);
if ($user) {
    $user->assignRole('super admin');
    echo "✅ User '$user->name' کو super admin بنا دیا گیا!\n";
    echo "اب سمبھی permissions ہوں گی۔\n";
} else {
    echo "❌ User ID $userId نہیں ملا۔\n";
    echo "اپنی صحیح user ID ڈالیں۔\n";
}
?>
