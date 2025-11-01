Step 3: How to Get the User's Chat ID (The Onboarding Process)
This is the process you will document for your pharmacists.
Create Your Bot: Go to Telegram and talk to the BotFather. Create a new bot for Careflux (e.g., CarefluxOpsBot). You will receive a Bot Token. Put this token in your .env file.
Instruct Your Pharmacist: Tell them to open Telegram and search for your bot's username (e.g., @CarefluxOpsBot).
The Magic Command: The simplest way is to have them send the message /start to your bot.
Retrieve the Chat ID: You (as the admin) need to see the message they sent. The easiest way is to use your browser. Go to this URL, replacing YOUR_BOT_TOKEN with the token you got from BotFather:
code
Code
https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
Find the ID: You will see a JSON response. Look for the message object from that user. Inside it, you will find a chat object with an id. This is their unique Chat ID.
code
JSON
{
  "ok": true,
  "result": [
    {
      "update_id": 123456789,
      "message": {
        "message_id": 1,
        "from": { ... },
        "chat": {
          "id": 987654321, // <-- THIS IS THE CHAT ID
          "first_name": "Pharmacist John",
          "type": "private"
        },
        "date": 1678886400,
        "text": "/start"
      }
    }
  ]
}
Save the ID: Copy this ID (987654321 in the example) and paste it into the "Telegram Chat ID" field on that user's profile in your Filament Admin Panel.