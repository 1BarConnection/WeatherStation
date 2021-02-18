/**
 * A brief description:
 * The device connects to the GPRS mobile network,
 * then collects data from sensors and sends
 * them to MySQL database on the web server.
 * After sending, the device goes into deep sleep mode.
 *
 * Prepared by 1BarConnection.
 * Based on https://bit.ly/2W8bYLr by Rui Santos.
 *
 */

#include <Arduino.h>

// GPRS credentials (leave blank if not required)
const char apn[] = "gprs"; // APN (example: gprs), check APN possible at https://wiki.apnchanger.org
const char gprsUser[] = ""; // GPRS user
const char gprsPass[] = ""; // GPRS password

// SIM card PIN (leave blank if not specified)
const char simPIN[] = "";

// Server details
// The server variable can be just a domain name or it can have a subdomain. Depending on the service you are using
const char server[] = "weather.example.com"; // domain name: example.com, maker.ifttt.com, etc.
const char resource[] = "/post-data2.php"; // path to php on the server, for example: /post-data.php
const int port = 80; // server port number

// The value of apiKeyValue must be the same as in the PHP file /post-data.php
String apiKeyValue = "00f2d801-371a-22fb-44d8-8b1e-006e056cba00";

// TTGO T-Call pins
#define MODEM_RST 5
#define MODEM_PWKEY 4
#define MODEM_POWER_ON 23
#define MODEM_TX 27
#define MODEM_RX 26
#define I2C_SDA 21
#define I2C_SCL 22
// BME280 pins
#define I2C_SDA_2 18
#define I2C_SCL_2 19
// GY1145 pins
#define I2C_SDA_3 18
#define I2C_SCL_3 19
// MAX17043 pins
// # define I2C_SDA_4 18
// # define I2C_SCL_4 19

// 1013.25 is sea level pressure value in hPA
#define SEALEVELPRESSURE_HPA (1013.25)

// Serial monitor, speed 115200 baud
#define SerialMon Serial
// For AT commands (up to SIM800 module)
#define SerialAT Serial1

// TinyGSM library configuration
#define TINY_GSM_MODEM_SIM800 // The modem is SIM800
#define TINY_GSM_RX_BUFFER 1024 // Setting the RX buffer to 1Kb

// Serial debug console declaration, if required
// # define DUMP_AT_COMMANDS

#include <Wire.h>
#include <TinyGsmClient.h>
#include <SparkFun_MAX1704x_Fuel_Gauge_Arduino_Library.h>

#ifdef DUMP_AT_COMMANDS
  #include <StreamDebugger.h>
  StreamDebugger debugger(SerialAT, SerialMon);
  TinyGsm modem(debugger);
#else
  TinyGsm modem(SerialAT);
#endif

#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
#include <Adafruit_SI1145.h>

// I2C for SIM800 (to work if powered by battery)
TwoWire I2CPower = TwoWire(0);

// I2C for BME280 sensor
TwoWire I2CBME = TwoWire(1);
Adafruit_BME280 bme;

// I2C for GY1145 sensor
TwoWire I2CGY = TwoWire(1);
Adafruit_SI1145 gy;

// I2C for MAX17043 sensor
//TwoWire I2CMAX = TwoWire (1);
//SFE_MAX1704X lipo;
//double soc = 0; // State-of-charge (SOC) battery charge variable

// TinyGSM client for internet connection
TinyGsmClient client(modem);

#define uS_TO_S_FACTOR 1000000 // Micro second to second conversion factor
// # define TIME_TO_SLEEP 3600 // Sleep time for ESP32 in seconds, 3600 seconds = 1 hour
#define TIME_TO_SLEEP 1800 // Sleep time for ESP32 in seconds, 1800 seconds = 30 minutes
// # define TIME_TO_SLEEP 60 // Sleep time for ESP32 in seconds, 60 seconds = 1 minute

#define IP5306_ADDR 0x75
#define IP5306_REG_SYS_CTL0 0x00

bool setPowerBoostKeepOn(int en) {
  I2CPower.beginTransmission(IP5306_ADDR);
  I2CPower.write(IP5306_REG_SYS_CTL0);
  if(en) {
    I2CPower.write(0x37); // Set bit1: 1 to enable or 0 to disable "boost keep on"
    I2CPower.write(0x35); // 0x37 is the default registry value
  }
  return I2CPower.endTransmission() == 0;
}

void setup() {
  // Serial debug monitor at 115200 baud
  SerialMon.begin(115200);

  // Start I2C communications
  I2CPower.begin(I2C_SDA, I2C_SCL, 400000);
  I2CBME.begin(I2C_SDA_2, I2C_SCL_2, 400000);
  I2CGY.begin(I2C_SDA_3, I2C_SCL_3, 400000);
  //I2CMAX.begin(I2C_SDA_4, I2C_SCL_4, 400000);

  // Enable a constant supply of power from the battery
  bool isOk = setPowerBoostKeepOn(1);
  SerialMon.println (String("IP5306 KeepOn") + (isOk? "OK": "not successful"));

  // Set pins for reset, enable, power
  pinMode(MODEM_PWKEY, OUTPUT);
  pinMode(MODEM_RST, OUTPUT);
  pinMode(MODEM_POWER_ON, OUTPUT);
  digitalWrite(MODEM_PWKEY, LOW);
  digitalWrite(MODEM_RST, HIGH);
  digitalWrite(MODEM_POWER_ON, HIGH);

  // GSM(GPRS) module speed in bauds and UART pins
  SerialAT.begin(115200, SERIAL_8N1, MODEM_RX, MODEM_TX);
  delay(3000);

  // To restart the SIM800 module (takes a lot of time)
  SerialMon.println("SIM800L modem initialization ...");
  modem.restart();
  // use modem.init () if you don't need a restart kit

  // Unlock the SIM card with a pin, if necessary
  if (strlen(simPIN) && modem.getSimStatus()!= 3) {
    modem.simUnlock(simPIN);
  }

  // Check the BME280 I2C address, in our case it is 0x76
  if (!bme.begin(0x76, & I2CBME)) {
    Serial.println("No BME280 sensor found, check connection!");
    while(1);
  }

  // Check the GY1145 I2C address, in our case it is 0x60
  if (!gy.begin(0x60, & I2CGY)) {
    Serial.println("GY1145 sensor not found, check connection!");
    while(1);
  }

  /*lipo.enableDebugging(); // Troubleshooting for MAX17043 in serial monitor

  // MAX17044:
  if(lipo.begin() == false) // Start (check for) MAX17044
  {
    Serial.println("MAX17043 sensor not found, check connection!");
    while(1);
  }

  // Perform a reset for the MAX17043 battery charge gauge
  lipo.reset();
  // Quick start restart MAX17043 to get more accurate SOC.
  lipo.quickStart(); */

  // The alarm source is configured as a timer alarm
  esp_sleep_enable_timer_wakeup(TIME_TO_SLEEP * uS_TO_S_FACTOR);
}

void loop() {
  SerialMon.print("Connect to APN:");
  SerialMon.print(apn);
  if (!modem.gprsConnect(apn, gprsUser, gprsPass)) {
    SerialMon.println("failed");
  }
  else {
    SerialMon.println("OK");

    SerialMon.print("Connect to");
    SerialMon.print(server);
    if(!client.connect (server, port)) {
      SerialMon.println("failed");
    }
    else {
      SerialMon.println("OK");

      float UVindex = gy.readUV();
      // The UV index is multiplied by 100 by default to divide the true value by 100
      UVindex /= 100.0;

      // Quick start restart MAX17043 to get more accurate SOC.
    /*lipo.quickStart ();

      // lipo.getSOC () returns "state of charge" batteries (eg 79%)
      soc = lipo.getSOC (); */

      // Make an HTTP POST request
      SerialMon.println("Executing HTTP POST request ...");
      // Prepare data for HTTP POST request (temperature in Celsius)
      String httpRequestData = "api_key=" +
      apiKeyValue + "&value2=" + String(bme.readHumidity()) + "&value1=" + String(bme.readTemperature()) +
      "&value3=" + String(bme.readPressure()/100.0F) + "&value4=" + String(UVindex) + "&value5=" + String("-") +
      "&value6=" + String(bme.readAltitude(SEALEVELPRESSURE_HPA)) + "";

      client.print(String("POST") + resource + "HTTP/1.1\r\n");
      client.print(String("Host:") + server + "\r\n");
      client.println("Connection: close");
      client.println("Content-Type: application/x-www-form-urlencoded");
      client.print("Content-Length:");
      client.println(httpRequestData.length());
      client.println();
      client.println(httpRequestData);

      unsigned long timeout = millis();
      while(client.connected() && millis() - timeout<10000L) {
        // Print access data (HTTP response from the server)
        while(client.available()) {
          char c = client.read();
          SerialMon.print(c);
          timeout = millis();
        }
      }
      SerialMon.println();

      // Close the client and disconnect
      client.stop();
      SerialMon.println("Connection to server closed");
      modem.gprsDisconnect();
      SerialMon.println("GPRS connection is closed");
    }
  }
  // Put ESP32 into sleep mode (let's wake up with a timer)
  esp_deep_sleep_start();
}
