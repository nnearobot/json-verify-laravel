import { useState} from 'react';
import axiosClient from '../axios-client.js';
import Button from '../components/Button';
import H1 from '../components/H1.jsx';

export default function UploadForm() {
  const [file, setFile] = useState(null)
  const [result, setResult] = useState(null)
  const [errors, setErrors] = useState(null)

  const onFileChange = event => {
      setFile(event.target.files[0]);
  };

  const onSubmit = event => {
    event.preventDefault();

    const formData = new FormData();

    formData.append(
      "json_file",
      file,
      file.name
    );

    axiosClient.post('/verify', formData)
      .then(response => {
        setResult(response.data.result);
      })
      .catch(err => {
        const response = err.response;
        if (response) {
          setErrors(response.data.errors)
            console.log(response.data.errors);
        }
      });

    return false;
  }

  return (
    <div className="h-[100vh] flex justify-center items-start">
      <div className="w-[100%] relative z-1 bg-white/40 p-6 shadow">
        <form onSubmit={onSubmit}>
          <H1>Upload an OpenAttestation file</H1>
          <div className="mt-10 mb-6"><input type="file" onChange={onFileChange} accept=".oa" /></div>
          <Button>Upload and verify</Button>
        </form>
        {errors &&
          <div className="text-rose-600 mt-5 text-lg">
            {Object.keys(errors).map(key => errors[key].map((err, ind) => <p key={key + ind}>{err}</p>))}
          </div>
        }
        {result &&
          <div className="text-green-600 mt-5 text-lg">{result}</div>
        }
      </div>
    </div>
  )
}
