<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SupportTicket;
use Carbon\Carbon;

class SupportTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tickets = [
            [
                'subject' => 'Incorrect route information for Bago Aplaya route',
                'message' => 'Good day! I noticed that the route information for Bago Aplaya is incorrect. The app shows that jeepneys stop at Victoria Plaza, but they actually stop at Roxas Avenue. This is causing confusion for passengers. Could you please update this information? Thank you!',
                'customer_name' => 'Maria Santos',
                'customer_email' => 'maria.santos@email.com',
                'type' => 'complaint',
                'priority' => 'high',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'subject' => 'Request to add new landmark - Abreeza Mall',
                'message' => 'Hello! I would like to suggest adding Abreeza Mall as a landmark in the app. It is a major shopping destination and many jeepney routes pass by it. Having it as a landmark would make navigation easier for users. The coordinates are approximately 7.0731° N, 125.6128° E.',
                'customer_name' => 'Juan Dela Cruz',
                'customer_email' => 'juan.delacruz@email.com',
                'type' => 'suggestion',
                'priority' => 'medium',
                'status' => 'in-progress',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'subject' => 'Jeepney fare inquiry',
                'message' => 'Hi, I would like to know the current fare for the Matina to Bankerohan route. Is it still ₱13.00 or has it been updated? Thank you for your assistance!',
                'customer_name' => 'Anna Reyes',
                'customer_email' => 'anna.reyes@email.com',
                'type' => 'inquiry',
                'priority' => 'low',
                'status' => 'resolved',
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'subject' => 'Update operating hours for Matina route',
                'message' => 'The operating hours displayed for the Matina route show 5:00 AM to 10:00 PM, but jeepneys are now running until 11:00 PM. Please update the schedule information to reflect the current operating hours.',
                'customer_name' => 'Pedro Garcia',
                'customer_email' => 'pedro.garcia@email.com',
                'type' => 'report',
                'priority' => 'high',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'subject' => 'Report missing jeepney on Toril route',
                'message' => 'I have been waiting at the Toril terminal for over 45 minutes and no jeepney has arrived for the city route. Is there a schedule change or are there fewer jeepneys operating today? Please advise.',
                'customer_name' => 'Sofia Martinez',
                'customer_email' => 'sofia.martinez@email.com',
                'type' => 'report',
                'priority' => 'medium',
                'status' => 'in-progress',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'subject' => 'Suggestion: Add real-time tracking feature',
                'message' => 'I love using this app! I have a suggestion - it would be great if you could add real-time tracking of jeepneys so we can see where they are and estimate arrival times. This would really improve the user experience!',
                'customer_name' => 'Carlos Ramos',
                'customer_email' => 'carlos.ramos@email.com',
                'type' => 'suggestion',
                'priority' => 'low',
                'status' => 'resolved',
                'created_at' => Carbon::now()->subDays(10),
            ],
            [
                'subject' => 'Wrong terminal information displayed',
                'message' => 'There is a bug in the app - the terminal information for Bo. Obrero route is showing as "Maa Centro" but it should be "Bago Aplaya". This is misleading and needs to be fixed as soon as possible.',
                'customer_name' => 'Linda Torres',
                'customer_email' => 'linda.torres@email.com',
                'type' => 'bug',
                'priority' => 'high',
                'status' => 'in-progress',
                'created_at' => Carbon::now()->subDays(4),
            ],
            [
                'subject' => 'App feedback - Navigation improvement',
                'message' => 'Overall, the app is very helpful! However, I think the navigation could be improved. The back button sometimes does not work properly, and it would be nice to have a home button to quickly return to the main screen.',
                'customer_name' => 'Roberto Cruz',
                'customer_email' => 'roberto.cruz@email.com',
                'type' => 'feedback',
                'priority' => 'low',
                'status' => 'resolved',
                'created_at' => Carbon::now()->subDays(14),
            ],
        ];

        foreach ($tickets as $ticket) {
            SupportTicket::create($ticket);
        }
    }
}
